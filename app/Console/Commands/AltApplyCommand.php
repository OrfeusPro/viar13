<?php

namespace App\Console\Commands;

use App\Models\ImageAltApplyLog;
use App\Models\ImageAltSuggestion;
use App\Services\AltGeneration\LocaleResolver;
use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;

/**
 * Applies approved generated alt/title values back to source content.
 */
class AltApplyCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'alt:apply
        {--auto : Apply generated rows when alt_generation.auto_apply is enabled}
        {--dry-run : Print changes without saving}
        {--force : Overwrite non-empty target values even when they were not written by this feature}
        {--ids= : Comma-separated suggestion ids to apply}
        {--locale= : Limit apply to one supported locale}
        {--limit=500 : Maximum suggestions to process}
        {--model= : Limit to one imageable model FQCN}
        {--source=cli : Audit source: cli, admin, or auto}
        {--applied-by= : User id for admin-triggered writes}
        {--status=approved : Comma-separated suggestion statuses to select}';

    /**
     * @var string
     */
    protected $description = 'Apply approved image alt/title suggestions to their source models';

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $altGenLogger;

    /**
     * @var \App\Services\AltGeneration\LocaleResolver
     */
    private $localeResolver;

    /**
     * @var array<int, array<int, string>>
     */
    private $dryRunRows = [];

    /**
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \App\Services\AltGeneration\LocaleResolver $localeResolver
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Illuminate\Log\LogManager $logManager
     */
    public function __construct(
        ConfigRepository $config,
        LocaleResolver $localeResolver,
        LoggerInterface $logger,
        LogManager $logManager
    ) {
        parent::__construct();

        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->logger = $logger;
        $this->altGenLogger = $logManager->channel('alt-gen');
    }

    /**
     * @return int
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $limit = max(0, (int) $this->option('limit'));
        $model = $this->resolveModelOption(trim((string) $this->option('model')));
        $locale = trim((string) $this->option('locale'));
        $ids = $this->idsFromOption((string) $this->option('ids'));
        $source = $this->sourceFromOption((string) $this->option('source'));
        $appliedBy = $this->appliedByFromOption($this->option('applied-by'));
        $statuses = $this->statusesFromOption((string) $this->option('status'));
        $autoApply = (bool) $this->option('auto') && (bool) $this->config->get('alt_generation.auto_apply', false);

        if ($autoApply) {
            $statuses = array_values(array_unique(array_merge($statuses, [ImageAltSuggestion::STATUS_GENERATED])));
        }

        if ($locale !== '' && !$this->localeResolver->isSupported($locale)) {
            $this->error('Unsupported locale for alt generation: ' . $locale);
            $this->line('Supported locales: ' . implode(', ', $this->localeResolver->supportedLocales()));

            return 1;
        }

        $locale = $locale === '' ? null : $this->localeResolver->normalizeOrDefault($locale);

        $query = ImageAltSuggestion::query()
            ->whereIn('status', $statuses)
            ->whereNotNull('imageable_type')
            ->whereNotNull('imageable_id')
            ->orderBy('id');

        if ($model !== '') {
            $query->where('imageable_type', $model);
        }

        if ($locale !== null) {
            $query->where('locale', $locale);
        }

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $suggestions = $query->get();

        $bar = $this->output->createProgressBar($suggestions->count());
        $bar->start();
        $applied = 0;

        foreach ($suggestions as $suggestion) {
            if ($this->applySuggestion($suggestion, $dryRun, $autoApply, $force, $source, $appliedBy)) {
                $applied++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');

        if ($dryRun && !empty($this->dryRunRows)) {
            $this->table(['id', 'model', 'field', 'column', 'old -> new'], $this->dryRunRows);
        }

        $this->info(($dryRun ? 'Dry-run applicable suggestions: ' : 'Applied suggestions: ') . $applied);

        return 0;
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param bool $dryRun
     * @param bool $autoApply
     * @param bool $force
     * @param string $source
     * @param int|null $appliedBy
     * @return bool
     */
    private function applySuggestion(
        ImageAltSuggestion $suggestion,
        bool $dryRun,
        bool $autoApply,
        bool $force,
        string $source,
        ?int $appliedBy
    ): bool
    {
        $locale = $this->localeResolver->normalizeOrDefault($suggestion->locale);
        $this->config->set('app.locale', $locale);
        app()->setLocale($locale);

        if (
            $suggestion->status !== ImageAltSuggestion::STATUS_APPROVED
            && !($autoApply && $suggestion->status === ImageAltSuggestion::STATUS_GENERATED)
        ) {
            $this->logSkip($suggestion, 'status is not approved');

            return false;
        }

        $entity = $this->resolveEntity($suggestion);

        if (!$entity) {
            return $this->skipAndFail($suggestion, 'imageable entity was not found', $dryRun, 'entity not found');
        }

        $alt = $this->approvedAlt($suggestion);
        $title = $this->approvedTitle($suggestion);

        if ($alt === null && $title === null) {
            return $this->skipAndFail($suggestion, 'approved alt/title is empty', $dryRun, 'empty approved value');
        }

        $sourceType = $this->sourceType($suggestion);
        $changed = $dryRun
            ? $this->applySuggestionWrites($entity, $suggestion, $sourceType, $alt, $title, true, $force, $source, $appliedBy)
            : DB::transaction(function () use ($entity, $suggestion, $sourceType, $alt, $title, $force, $source, $appliedBy): bool {
                return $this->applySuggestionWrites(
                    $entity,
                    $suggestion,
                    $sourceType,
                    $alt,
                    $title,
                    false,
                    $force,
                    $source,
                    $appliedBy
                );
            });

        if (!$changed || $dryRun) {
            return $changed;
        }

        $suggestion->setStatus(ImageAltSuggestion::STATUS_APPLIED);
        $suggestion->applied_at = Carbon::now();
        $suggestion->error = null;
        $suggestion->save();

        return true;
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private function resolveEntity(ImageAltSuggestion $suggestion): ?Model
    {
        $class = (string) $suggestion->imageable_type;

        if (!class_exists($class) || !is_subclass_of($class, Model::class)) {
            return null;
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $class */
        $entity = $class::query()->find($suggestion->imageable_id);

        return $entity instanceof Model ? $entity : null;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param string $field
     * @param string $locale
     * @return mixed
     */
    private function readEntityAttribute(Model $entity, string $field, string $locale)
    {
        if (method_exists($entity, 'getTranslatedAttribute')) {
            try {
                $translated = $entity->getTranslatedAttribute(
                    $field,
                    $locale,
                    $this->localeResolver->defaultLocale()
                );

                if ($translated !== null) {
                    return $translated;
                }
            } catch (\Throwable $exception) {
                // Fall back to the base attribute below.
            }
        }

        if (array_key_exists($field, $entity->getAttributes())) {
            return $entity->getAttribute($field);
        }

        $value = $entity->getAttribute($field);

        return $value !== null ? $value : null;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param string $field
     * @param string $value
     * @param string $locale
     * @return void
     */
    private function writeEntityAttribute(Model $entity, string $field, string $value, string $locale): void
    {
        if (method_exists($entity, 'translateOrNew')) {
            try {
                $translation = $entity->translateOrNew($locale);

                if (is_object($translation)) {
                    $translation->{$field} = $value;

                    if (method_exists($translation, 'save')) {
                        $translation->save();

                        return;
                    }
                }
            } catch (\Throwable $exception) {
                // Fall back to writing the base attribute below.
            }
        }

        $entity->setAttribute($field, $value);
        $entity->save();
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return string|null
     */
    private function approvedAlt(ImageAltSuggestion $suggestion): ?string
    {
        return $suggestion->approved_alt !== null ? $suggestion->approved_alt : $suggestion->suggested_alt;
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return string|null
     */
    private function approvedTitle(ImageAltSuggestion $suggestion): ?string
    {
        return $suggestion->approved_title !== null ? $suggestion->approved_title : $suggestion->suggested_title;
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return string
     */
    private function sourceType(ImageAltSuggestion $suggestion): string
    {
        $context = (array) $suggestion->prompt_context;
        $image = isset($context['image']) && is_array($context['image']) ? $context['image'] : [];

        return (string) ($image['source_type'] ?? 'field');
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return string|null
     */
    private function sourceField(ImageAltSuggestion $suggestion): ?string
    {
        $context = (array) $suggestion->prompt_context;
        $image = isset($context['image']) && is_array($context['image']) ? $context['image'] : [];
        $field = $image['field'] ?? $suggestion->field;

        return is_string($field) && $field !== '' ? $field : null;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string $sourceType
     * @param string|null $alt
     * @param string|null $title
     * @param bool $dryRun
     * @param bool $force
     * @param string $source
     * @param int|null $appliedBy
     * @return bool
     */
    private function applySuggestionWrites(
        Model $entity,
        ImageAltSuggestion $suggestion,
        string $sourceType,
        ?string $alt,
        ?string $title,
        bool $dryRun,
        bool $force,
        string $source,
        ?int $appliedBy
    ): bool {
        if ($sourceType === 'html') {
            return $this->applyHtmlSuggestion($entity, $suggestion, $alt, $title, $dryRun, $force, $source, $appliedBy);
        }

        if ($sourceType === 'media') {
            return $this->applyMediaSuggestion($entity, $suggestion, $alt, $title, $dryRun, $force, $source, $appliedBy);
        }

        return $this->applyFieldSuggestion($entity, $suggestion, $alt, $title, $dryRun, $force, $source, $appliedBy);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string|null $alt
     * @param string|null $title
     * @param bool $dryRun
     * @param bool $force
     * @param string $source
     * @param int|null $appliedBy
     * @return bool
     */
    private function applyFieldSuggestion(
        Model $entity,
        ImageAltSuggestion $suggestion,
        ?string $alt,
        ?string $title,
        bool $dryRun,
        bool $force,
        string $source,
        ?int $appliedBy
    ): bool {
        $field = $this->sourceField($suggestion);

        if ($field === null) {
            return $this->skipAndFail($suggestion, 'source field is empty', $dryRun, 'no target column');
        }

        $table = $entity->getTable();
        $schema = $entity->getConnection()->getSchemaBuilder();
        $map = $this->applyMap($entity, $field);
        $altColumn = $map['alt'] ?? null;
        $titleColumn = $map['title'] ?? null;
        $locale = $this->localeResolver->normalizeOrDefault($suggestion->locale);

        if ($this->shouldUseVirtualLocaleApply(
            $entity,
            $schema,
            $table,
            $altColumn,
            $titleColumn,
            $locale,
            $alt !== null,
            $title !== null
        )) {
            return $this->virtualApply($suggestion, $field, ['locale-specific SiteImage columns are absent'], $dryRun);
        }

        $altColumn = $this->localizedColumnIfExists($schema, $table, $altColumn, $locale);
        $titleColumn = $this->localizedColumnIfExists($schema, $table, $titleColumn, $locale);
        $missing = [];

        if ($alt !== null && ($altColumn === null || !$schema->hasColumn($table, $altColumn))) {
            $missing[] = $altColumn ?: $field . '_alt';
        }

        if ($title !== null && ($titleColumn === null || !$schema->hasColumn($table, $titleColumn))) {
            $missing[] = $titleColumn ?: $field . '_title';
        }

        if (!empty($missing)) {
            return $this->virtualApply($suggestion, $field, $missing, $dryRun);
        }

        $writes = [];

        if ($alt !== null && $altColumn !== null) {
            $writes[] = [
                'column' => $altColumn,
                'value' => $alt,
            ];
        }

        if ($title !== null && $titleColumn !== null) {
            $writes[] = [
                'column' => $titleColumn,
                'value' => $title,
            ];
        }

        return $this->applyColumnWrites($entity, $suggestion, $field, $writes, $dryRun, $force, $source, $appliedBy);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string|null $alt
     * @param string|null $title
     * @param bool $dryRun
     * @param bool $force
     * @param string $source
     * @param int|null $appliedBy
     * @return bool
     */
    private function applyMediaSuggestion(
        Model $entity,
        ImageAltSuggestion $suggestion,
        ?string $alt,
        ?string $title,
        bool $dryRun,
        bool $force,
        string $source,
        ?int $appliedBy
    ): bool {
        $field = $this->sourceField($suggestion);

        if ($field === null) {
            return $this->skipAndFail($suggestion, 'media source field is empty', $dryRun, 'media source field is empty');
        }

        $image = $this->imageContext($suggestion);
        $locale = $this->localeResolver->normalizeOrDefault($suggestion->locale);
        $collection = isset($image['collection_name']) && is_string($image['collection_name'])
            ? $image['collection_name']
            : null;

        if ($collection === null && strpos($field, 'media:') === 0) {
            $collection = substr($field, 6);
        }

        $mediaId = isset($image['media_id']) && (int) $image['media_id'] > 0 ? (int) $image['media_id'] : null;
        $media = $this->resolveMediaItem($entity, $collection, $mediaId, (string) $suggestion->image_path);

        if ($media === null) {
            return $this->skipAndFail($suggestion, 'MediaLibrary item was not found', $dryRun, 'media item not found');
        }

        if (!method_exists($media, 'setCustomProperty') || !method_exists($media, 'save')) {
            return $this->skipAndFail($suggestion, 'MediaLibrary item cannot be updated', $dryRun, 'media item not writable');
        }

        $altKey = isset($image['custom_alt_key']) && is_string($image['custom_alt_key'])
            ? $image['custom_alt_key']
            : 'image_alt_' . $locale;
        $titleKey = isset($image['custom_title_key']) && is_string($image['custom_title_key'])
            ? $image['custom_title_key']
            : 'image_title_' . $locale;
        $writes = [];

        if ($alt !== null) {
            $writes[] = [
                'column' => $altKey,
                'value' => $alt,
            ];
        }

        if ($title !== null && trim($title) !== '') {
            $writes[] = [
                'column' => $titleKey,
                'value' => $title,
            ];
        }

        if (empty($writes)) {
            return true;
        }

        $pendingWrites = [];

        foreach ($writes as $write) {
            $oldValue = (string) ($this->mediaCustomProperty($media, $write['column']) ?? '');
            $newValue = (string) $write['value'];

            if ($oldValue === $newValue) {
                continue;
            }

            if (!$this->canOverwriteMediaValue($suggestion, $field, $write['column'], $oldValue, $force)) {
                return $this->skipAndFail(
                    $suggestion,
                    'media custom property has non-empty non-owned value: ' . $write['column'],
                    $dryRun,
                    'media custom property has non-owned value'
                );
            }

            $pendingWrites[] = [
                'column' => $write['column'],
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        if (empty($pendingWrites)) {
            return true;
        }

        foreach ($pendingWrites as $write) {
            if ($dryRun) {
                $this->addDryRunRow($suggestion, $field, $write['column'], $write['old'], $write['new']);
                continue;
            }

            $media->setCustomProperty($write['column'], $write['new']);
        }

        if (!$dryRun) {
            $media->save();

            foreach ($pendingWrites as $write) {
                $this->logWrite($suggestion, $field, $write['column'], $write['old'], $write['new'], $source, $appliedBy);
            }
        }

        return true;
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string $field
     * @param string $column
     * @param string $oldValue
     * @param bool $force
     * @return bool
     */
    private function canOverwriteMediaValue(
        ImageAltSuggestion $suggestion,
        string $field,
        string $column,
        string $oldValue,
        bool $force
    ): bool {
        if ($this->canOverwriteValue($suggestion, $field, $column, $oldValue, $force)) {
            return true;
        }

        $image = $this->imageContext($suggestion);
        $locale = $this->localeResolver->normalizeOrDefault($suggestion->locale);
        $altKey = isset($image['custom_alt_key']) && is_string($image['custom_alt_key'])
            ? $image['custom_alt_key']
            : 'image_alt_' . $locale;
        $titleKey = isset($image['custom_title_key']) && is_string($image['custom_title_key'])
            ? $image['custom_title_key']
            : 'image_title_' . $locale;

        if ($column === $altKey && $suggestion->current_alt !== null) {
            return $oldValue === (string) $suggestion->current_alt;
        }

        if ($column === $titleKey && $suggestion->current_title !== null) {
            return $oldValue === (string) $suggestion->current_title;
        }

        return false;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string $field
     * @param array<int, array{column: string, value: string}> $writes
     * @param bool $dryRun
     * @param bool $force
     * @param string $source
     * @param int|null $appliedBy
     * @return bool
     */
    private function applyColumnWrites(
        Model $entity,
        ImageAltSuggestion $suggestion,
        string $field,
        array $writes,
        bool $dryRun,
        bool $force,
        string $source,
        ?int $appliedBy
    ): bool {
        $pendingWrites = [];

        foreach ($writes as $write) {
            $column = $write['column'];
            $newValue = $write['value'];
            $oldValue = (string) $entity->getAttribute($column);

            if ($oldValue === $newValue) {
                continue;
            }

            if (!$this->canOverwriteValue($suggestion, $field, $column, $oldValue, $force)) {
                return $this->skipAndFail(
                    $suggestion,
                    'target column has non-empty non-owned value: ' . $column,
                    $dryRun,
                    'target column has non-owned value'
                );
            }

            $pendingWrites[] = [
                'column' => $column,
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        if (empty($pendingWrites)) {
            return true;
        }

        foreach ($pendingWrites as $write) {
            if ($dryRun) {
                $this->addDryRunRow($suggestion, $field, $write['column'], $write['old'], $write['new']);
                continue;
            }

            $entity->setAttribute($write['column'], $write['new']);
            $entity->save();
            $this->logWrite($suggestion, $field, $write['column'], $write['old'], $write['new'], $source, $appliedBy);
        }

        return true;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string|null $alt
     * @param string|null $title
     * @param bool $dryRun
     * @param bool $force
     * @param string $source
     * @param int|null $appliedBy
     * @return bool
     */
    private function applyHtmlSuggestion(
        Model $entity,
        ImageAltSuggestion $suggestion,
        ?string $alt,
        ?string $title,
        bool $dryRun,
        bool $force,
        string $source,
        ?int $appliedBy
    ): bool {
        $field = $this->sourceField($suggestion);

        if ($field === null) {
            return $this->skipAndFail($suggestion, 'HTML target field is missing', $dryRun, 'no target column');
        }

        if (!$this->isConfiguredHtmlField($entity, $field)) {
            return $this->skipAndFail(
                $suggestion,
                'HTML target field is not configured for alt generation',
                $dryRun,
                'no target column'
            );
        }

        $locale = $this->localeResolver->normalizeOrDefault($suggestion->locale);
        $html = $this->readEntityAttribute($entity, $field, $locale);

        if ($html === null) {
            return $this->skipAndFail($suggestion, 'HTML target field is missing', $dryRun, 'no target column');
        }

        $html = (string) $html;
        $result = $this->rewriteHtml($html, $suggestion, $alt, $title, $force);

        if (isset($result['error'])) {
            return $this->skipAndFail($suggestion, $result['error'], $dryRun, $result['error']);
        }

        $updated = (string) ($result['html'] ?? $html);

        if ($updated === $html) {
            return true;
        }

        if ($dryRun) {
            $this->addDryRunRow($suggestion, $field, $field, $html, $updated);

            return true;
        }

        $this->writeEntityAttribute($entity, $field, $updated, $locale);
        $this->logWrite($suggestion, $field, $field, $html, $updated, $source, $appliedBy);

        return true;
    }

    /**
     * @param string $html
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string|null $alt
     * @param string|null $title
     * @param bool $force
     * @return array{html?: string, error?: string}
     */
    private function rewriteHtml(
        string $html,
        ImageAltSuggestion $suggestion,
        ?string $alt,
        ?string $title,
        bool $force
    ): array
    {
        $context = (array) $suggestion->prompt_context;
        $image = isset($context['image']) && is_array($context['image']) ? $context['image'] : [];
        $xpathValue = isset($image['xpath']) ? (string) $image['xpath'] : '';
        $field = isset($image['field']) && is_string($image['field']) ? $image['field'] : (string) $suggestion->field;

        if ($xpathValue === '') {
            return ['error' => 'HTML image xpath is missing'];
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $nodes = $xpath->query($xpathValue);

        if ($nodes === false || $nodes->length === 0) {
            $nodes = $xpath->query('//img');
        }

        if ($nodes === false) {
            return ['error' => 'matching HTML image was not found'];
        }

        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement || $node->nodeName !== 'img') {
                continue;
            }

            if ($node->getAttribute('src') !== '' && $xpathValue !== '' && $nodes->length > 1) {
                if ($this->normalizeImagePath($node->getAttribute('src')) !== $suggestion->image_path) {
                    continue;
                }
            }

            if ($alt !== null) {
                $currentAlt = $node->hasAttribute('alt') ? $node->getAttribute('alt') : '';

                if (!$this->canOverwriteValue($suggestion, $field, $field, $html, $force, $currentAlt, $alt, 'alt')) {
                    return ['error' => 'HTML image alt has non-empty non-owned value'];
                }

                $node->setAttribute('alt', $alt);
            }

            if ($title !== null) {
                $currentTitle = $node->hasAttribute('title') ? $node->getAttribute('title') : '';

                if (!$this->canOverwriteValue(
                    $suggestion,
                    $field,
                    $field,
                    $html,
                    $force,
                    $currentTitle,
                    $title,
                    'title'
                )) {
                    return ['error' => 'HTML image title has non-empty non-owned value'];
                }

                $node->setAttribute('title', $title);
            }

            $updated = $document->saveHTML();

            return ['html' => $this->stripEncodingProcessingInstruction((string) $updated)];
        }

        return ['error' => 'matching HTML image was not found'];
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string $field
     * @param string $column
     * @param string $oldValue
     * @param bool $force
     * @param string|null $currentValue
     * @param string|null $newValue
     * @param string|null $htmlAttribute
     * @return bool
     */
    private function canOverwriteValue(
        ImageAltSuggestion $suggestion,
        string $field,
        string $column,
        string $oldValue,
        bool $force,
        ?string $currentValue = null,
        ?string $newValue = null,
        ?string $htmlAttribute = null
    ): bool {
        $valueToCheck = $currentValue !== null ? $currentValue : $oldValue;

        if ($force || trim($valueToCheck) === '') {
            return true;
        }

        if ($newValue !== null && $valueToCheck === $newValue) {
            return true;
        }

        if ($currentValue !== null && $htmlAttribute !== null) {
            return $this->ownsExistingHtmlAttribute($suggestion, $field, $htmlAttribute, $currentValue);
        }

        return $this->ownsExistingValue($suggestion, $field, $column, $oldValue);
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string $field
     * @param string $attribute
     * @param string $currentValue
     * @return bool
     */
    private function ownsExistingHtmlAttribute(
        ImageAltSuggestion $suggestion,
        string $field,
        string $attribute,
        string $currentValue
    ): bool {
        if (trim($currentValue) === '') {
            return false;
        }

        $query = ImageAltApplyLog::query()
            ->where('suggestion_id', $suggestion->id)
            ->where('field', $field)
            ->where('column_written', $field)
            ->orderByDesc('id')
            ->limit(25);

        if ($this->applyLogHasColumn('locale')) {
            $query->where('locale', $suggestion->locale);
        }

        $logs = $query->get();

        foreach ($logs as $log) {
            if (!$log instanceof ImageAltApplyLog || $log->new_value === null) {
                continue;
            }

            $loggedValue = $this->htmlAttributeValue((string) $log->new_value, $suggestion, $attribute);

            if ($loggedValue !== null && $loggedValue === $currentValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $html
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string $attribute
     * @return string|null
     */
    private function htmlAttributeValue(string $html, ImageAltSuggestion $suggestion, string $attribute): ?string
    {
        $context = (array) $suggestion->prompt_context;
        $image = isset($context['image']) && is_array($context['image']) ? $context['image'] : [];
        $xpathValue = isset($image['xpath']) ? (string) $image['xpath'] : '';

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $nodes = $xpathValue !== '' ? $xpath->query($xpathValue) : false;

        if ($nodes === false || $nodes->length === 0) {
            $nodes = $xpath->query('//img');
        }

        if ($nodes === false) {
            return null;
        }

        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement || $node->nodeName !== 'img') {
                continue;
            }

            if ($nodes->length > 1 && $node->getAttribute('src') !== '') {
                if ($this->normalizeImagePath($node->getAttribute('src')) !== $suggestion->image_path) {
                    continue;
                }
            }

            return $node->hasAttribute($attribute) ? $node->getAttribute($attribute) : '';
        }

        return null;
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string $field
     * @param string $column
     * @param string $oldValue
     * @return bool
     */
    private function ownsExistingValue(
        ImageAltSuggestion $suggestion,
        string $field,
        string $column,
        string $oldValue
    ): bool {
        if (trim($oldValue) === '') {
            return false;
        }

        $query = ImageAltApplyLog::query()
            ->where('imageable_type', $suggestion->imageable_type)
            ->where('imageable_id', $suggestion->imageable_id)
            ->where('field', $field)
            ->where('column_written', $column)
            ->where('new_value', $oldValue);

        if ($this->applyLogHasColumn('locale')) {
            $query->where('locale', $suggestion->locale);
        }

        return $query->exists();
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string $field
     * @param string $column
     * @param string|null $oldValue
     * @param string|null $newValue
     * @param string $source
     * @param int|null $appliedBy
     * @return void
     */
    private function logWrite(
        ImageAltSuggestion $suggestion,
        string $field,
        string $column,
        ?string $oldValue,
        ?string $newValue,
        string $source,
        ?int $appliedBy
    ): void {
        $attributes = [
            'suggestion_id' => $suggestion->id,
            'imageable_type' => $suggestion->imageable_type,
            'imageable_id' => $suggestion->imageable_id,
            'field' => $field,
            'locale' => $suggestion->locale,
            'column_written' => $column,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'applied_by' => $appliedBy,
            'applied_at' => Carbon::now(),
            'source' => $source,
        ];
        $image = $this->imageContext($suggestion);
        $sourceType = isset($image['source_type']) && is_string($image['source_type']) ? $image['source_type'] : null;
        $mediaId = isset($image['media_id']) && (int) $image['media_id'] > 0 ? (int) $image['media_id'] : null;

        if (!$this->applyLogHasColumn('locale')) {
            unset($attributes['locale']);
        }

        if ($sourceType !== null && $this->applyLogHasColumn('source_type')) {
            $attributes['source_type'] = $sourceType;
        }

        if ($mediaId !== null && $this->applyLogHasColumn('media_id')) {
            $attributes['media_id'] = $mediaId;
        }

        $log = ImageAltApplyLog::create($attributes);

        $this->altGenLogger->info('Applied image alt suggestion write.', [
            'apply_log_id' => $log->id,
            'suggestion_id' => $suggestion->id,
            'imageable_type' => $suggestion->imageable_type,
            'imageable_id' => $suggestion->imageable_id,
            'field' => $field,
            'locale' => $suggestion->locale,
            'column_written' => $column,
            'source' => $source,
            'source_type' => $sourceType,
            'media_id' => $mediaId,
            'applied_by' => $appliedBy,
        ]);
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string $field
     * @param string $column
     * @param string $oldValue
     * @param string $newValue
     * @return void
     */
    private function addDryRunRow(
        ImageAltSuggestion $suggestion,
        string $field,
        string $column,
        string $oldValue,
        string $newValue
    ): void {
        $this->dryRunRows[] = [
            (string) $suggestion->id,
            class_basename((string) $suggestion->imageable_type) . '#' . (string) $suggestion->imageable_id,
            $field,
            $column,
            $this->shortValue($oldValue) . ' -> ' . $this->shortValue($newValue),
        ];
    }

    /**
     * Mark a field suggestion as applicable without writing to source columns.
     *
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string $field
     * @param array<int, string> $missing
     * @param bool $dryRun
     * @return bool
     */
    private function virtualApply(ImageAltSuggestion $suggestion, string $field, array $missing, bool $dryRun): bool
    {
        $message = 'virtual apply via image_alt_suggestions; missing target column(s): ' . implode(', ', $missing);

        if ($dryRun) {
            $this->dryRunRows[] = [
                (string) $suggestion->id,
                class_basename((string) $suggestion->imageable_type) . '#' . (string) $suggestion->imageable_id,
                $field,
                'virtual',
                $message,
            ];
        }

        $this->altGenLogger->info('Virtual image alt suggestion apply.', [
            'suggestion_id' => $suggestion->id,
            'imageable_type' => $suggestion->imageable_type,
            'imageable_id' => $suggestion->imageable_id,
            'image_path' => $suggestion->image_path,
            'field' => $field,
            'locale' => $suggestion->locale,
            'missing_columns' => $missing,
        ]);

        return true;
    }

    /**
     * @param string $value
     * @return string
     */
    private function shortValue(string $value): string
    {
        $value = trim((string) preg_replace('/\s+/', ' ', $value));

        if (function_exists('mb_strlen') && mb_strlen($value, 'UTF-8') > 90) {
            return mb_substr($value, 0, 87, 'UTF-8') . '...';
        }

        if (!function_exists('mb_strlen') && strlen($value) > 90) {
            return substr($value, 0, 87) . '...';
        }

        return $value;
    }

    /**
     * @param string $value
     * @return array<int, int>
     */
    private function idsFromOption(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(static function ($id): int {
            return (int) trim((string) $id);
        }, explode(',', $value)))));
    }

    /**
     * @param string $value
     * @return string
     */
    private function sourceFromOption(string $value): string
    {
        $value = trim($value);

        return in_array($value, ['cli', 'admin', 'auto'], true) ? $value : 'cli';
    }

    /**
     * @param mixed $value
     * @return int|null
     */
    private function appliedByFromOption($value): ?int
    {
        $id = (int) $value;

        return $id > 0 ? $id : null;
    }

    /**
     * @param string $value
     * @return array<int, string>
     */
    private function statusesFromOption(string $value): array
    {
        $statuses = array_values(array_filter(array_map('trim', explode(',', $value))));

        return empty($statuses) ? [ImageAltSuggestion::STATUS_APPROVED] : $statuses;
    }

    /**
     * @param string $model
     * @return string
     */
    private function resolveModelOption(string $model): string
    {
        if ($model === '' || class_exists($model)) {
            return $model;
        }

        foreach (array_keys((array) $this->config->get('alt_generation.targets', [])) as $class) {
            if (is_string($class) && class_basename($class) === $model) {
                return $class;
            }
        }

        return $model;
    }

    /**
     * @param mixed $schema
     * @param string $table
     * @param string|null $column
     * @param string $locale
     * @return string|null
     */
    private function localizedColumnIfExists($schema, string $table, ?string $column, string $locale): ?string
    {
        if ($column === null || $column === '') {
            return $column;
        }

        $localized = $column . '_' . $locale;

        return $schema->hasColumn($table, $localized) ? $localized : $column;
    }

    /**
     * @param string $column
     * @return bool
     */
    private function applyLogHasColumn(string $column): bool
    {
        $model = new ImageAltApplyLog();

        return $model->getConnection()
            ->getSchemaBuilder()
            ->hasColumn($model->getTable(), $column);
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return array<string, mixed>
     */
    private function imageContext(ImageAltSuggestion $suggestion): array
    {
        $context = (array) $suggestion->prompt_context;
        $image = isset($context['image']) && is_array($context['image']) ? $context['image'] : [];

        return $image;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param string|null $collection
     * @param int|null $mediaId
     * @param string $imagePath
     * @return mixed|null
     */
    private function resolveMediaItem(Model $entity, ?string $collection, ?int $mediaId, string $imagePath)
    {
        if ($collection === null || $collection === '' || !method_exists($entity, 'getMedia')) {
            return null;
        }

        try {
            $items = $entity->getMedia($collection);
        } catch (\Throwable $exception) {
            return null;
        }

        if (!is_iterable($items)) {
            return null;
        }

        $normalizedImagePath = $this->normalizeImagePath($imagePath);

        foreach ($items as $media) {
            if (!is_object($media)) {
                continue;
            }

            if ($mediaId !== null && isset($media->id) && (int) $media->id === $mediaId) {
                return $media;
            }

            if (!method_exists($media, 'getUrl')) {
                continue;
            }

            try {
                $mediaPath = $this->normalizeImagePath((string) $media->getUrl());
            } catch (\Throwable $exception) {
                continue;
            }

            if ($mediaPath === $normalizedImagePath) {
                return $media;
            }
        }

        return null;
    }

    /**
     * @param mixed $media
     * @param string $key
     * @return string|null
     */
    private function mediaCustomProperty($media, string $key): ?string
    {
        if (!is_object($media) || !method_exists($media, 'getCustomProperty')) {
            return null;
        }

        try {
            $value = $media->getCustomProperty($key);
        } catch (\Throwable $exception) {
            return null;
        }

        if ($value === null || is_array($value) || is_object($value)) {
            return null;
        }

        return (string) $value;
    }

    /**
     * Keep SiteImage locale-specific values in image_alt_suggestions unless real locale columns exist.
     *
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param mixed $schema
     * @param string $table
     * @param string|null $altColumn
     * @param string|null $titleColumn
     * @param string $locale
     * @param bool $hasAlt
     * @param bool $hasTitle
     * @return bool
     */
    private function shouldUseVirtualLocaleApply(
        Model $entity,
        $schema,
        string $table,
        ?string $altColumn,
        ?string $titleColumn,
        string $locale,
        bool $hasAlt,
        bool $hasTitle
    ): bool {
        if (!$entity instanceof \App\Models\SiteImage) {
            return false;
        }

        if ($hasAlt && !$this->localizedColumnExists($schema, $table, $altColumn, $locale)) {
            return true;
        }

        if ($hasTitle && !$this->localizedColumnExists($schema, $table, $titleColumn, $locale)) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $schema
     * @param string $table
     * @param string|null $column
     * @param string $locale
     * @return bool
     */
    private function localizedColumnExists($schema, string $table, ?string $column, string $locale): bool
    {
        if ($column === null || $column === '') {
            return false;
        }

        return $schema->hasColumn($table, $column . '_' . $locale);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param string $field
     * @return array{alt: string|null, title: string|null}
     */
    private function applyMap(Model $entity, string $field): array
    {
        $target = $this->targetForEntity($entity);
        $map = isset($target['apply_map'][$field]) && is_array($target['apply_map'][$field])
            ? $target['apply_map'][$field]
            : null;

        if ($map !== null) {
            return [
                'alt' => isset($map['alt']) && is_string($map['alt']) && $map['alt'] !== '' ? $map['alt'] : null,
                'title' => isset($map['title']) && is_string($map['title']) && $map['title'] !== '' ? $map['title'] : null,
            ];
        }

        $altColumn = $field . '_alt';

        if (method_exists($entity, 'altFieldFor')) {
            $resolved = $entity->altFieldFor($field);

            if (is_string($resolved) && $resolved !== '') {
                $altColumn = $resolved;
            }
        }

        return [
            'alt' => $altColumn,
            'title' => $field . '_title',
        ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @return array<string, mixed>
     */
    private function targetForEntity(Model $entity): array
    {
        $targets = (array) $this->config->get('alt_generation.targets', []);
        $entityClass = get_class($entity);

        if (isset($targets[$entityClass]) && is_array($targets[$entityClass])) {
            return $targets[$entityClass];
        }

        foreach ($targets as $class => $target) {
            if (is_string($class) && $entity instanceof $class && is_array($target)) {
                return $target;
            }
        }

        return [];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param string $field
     * @return bool
     */
    private function isConfiguredHtmlField(Model $entity, string $field): bool
    {
        $target = $this->targetForEntity($entity);
        $htmlFields = isset($target['html_fields']) && is_array($target['html_fields'])
            ? $target['html_fields']
            : [];

        return in_array($field, $htmlFields, true);
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string $reason
     * @param bool $dryRun
     * @param string $error
     * @return bool
     */
    private function skipAndFail(ImageAltSuggestion $suggestion, string $reason, bool $dryRun, string $error): bool
    {
        $this->logSkip($suggestion, $reason);

        if (!$dryRun) {
            $suggestion->setStatus(ImageAltSuggestion::STATUS_FAILED);
            $suggestion->error = $error;
            $suggestion->save();
        }

        return false;
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string $reason
     * @return void
     */
    private function logSkip(ImageAltSuggestion $suggestion, string $reason): void
    {
        $message = 'Skipping alt suggestion #' . $suggestion->id . ': ' . $reason;

        $this->warn($message);
        $this->logger->warning('Skipping alt suggestion apply.', [
            'suggestion_id' => $suggestion->id,
            'imageable_type' => $suggestion->imageable_type,
            'imageable_id' => $suggestion->imageable_id,
            'image_path' => $suggestion->image_path,
            'field' => $suggestion->field,
            'reason' => $reason,
        ]);
    }

    /**
     * @param string $html
     * @return string
     */
    private function stripEncodingProcessingInstruction(string $html): string
    {
        return (string) preg_replace('/^<\?xml encoding="utf-8" \?>\s*/', '', $html);
    }

    /**
     * @param string $path
     * @return string
     */
    private function normalizeImagePath(string $path): string
    {
        $path = trim(html_entity_decode($path, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $parts = preg_split('/[?#]/', $path, 2);
        $path = str_replace('\\', '/', $parts[0] ?? $path);

        if (preg_match('#^https?://#i', $path)) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = ltrim($path, '/');

        return strpos($path, 'storage/') === 0 ? substr($path, 8) : $path;
    }
}
