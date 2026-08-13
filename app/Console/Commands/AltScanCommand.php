<?php

namespace App\Console\Commands;

use App\Models\ImageAltSuggestion;
use Carbon\Carbon;
use App\Services\AltGeneration\ImageCollector;
use App\Services\AltGeneration\ImageDescriptor;
use App\Services\AltGeneration\LocaleResolver;
use App\Services\AltGeneration\PageContextResolver;
use App\Services\AltGeneration\SkippedAltGenerationLogger;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Scans configured models for images and creates new suggestion rows.
 */
class AltScanCommand extends Command
{
    private const INSERT_BATCH_SIZE = 500;

    /**
     * @var string
     */
    protected $signature = 'alt:scan
        {--model= : Limit to one imageable model FQCN or class basename}
        {--since= : Scan rows updated since this date}
        {--locale= : Limit scan to one supported locale}
        {--limit=0 : Maximum image rows to scan; 0 means unlimited}
        {--dry-run : Show rows that would be created or reset without writing to the database}';

    /**
     * @var string
     */
    protected $description = 'Scan configured models for images that need alt/title generation';

    /**
     * @var \App\Services\AltGeneration\ImageCollector
     */
    private $collector;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \App\Services\AltGeneration\PageContextResolver
     */
    private $contextResolver;

    /**
     * @var \App\Services\AltGeneration\LocaleResolver
     */
    private $localeResolver;

    /**
     * @var \App\Services\AltGeneration\SkippedAltGenerationLogger
     */
    private $skippedLogger;

    /**
     * @var array<int, array<string, mixed>>
     */
    private $pendingRows = [];

    /**
     * @var array<int, array<int, string>>
     */
    private $debugRows = [];

    /**
     * @var bool
     */
    private $dryRun = false;

    /**
     * @var int
     */
    private $limit = 0;

    /**
     * @var int
     */
    private $prepared = 0;

    /**
     * @var int
     */
    private $scannedRows = 0;

    /**
     * @var bool
     */
    private $hasLocaleColumn = false;

    /**
     * @param \App\Services\AltGeneration\ImageCollector $collector
     * @param \App\Services\AltGeneration\PageContextResolver $contextResolver
     * @param \App\Services\AltGeneration\LocaleResolver $localeResolver
     * @param \App\Services\AltGeneration\SkippedAltGenerationLogger $skippedLogger
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(
        ImageCollector $collector,
        PageContextResolver $contextResolver,
        LocaleResolver $localeResolver,
        SkippedAltGenerationLogger $skippedLogger,
        ConfigRepository $config
    ) {
        parent::__construct();

        $this->collector = $collector;
        $this->contextResolver = $contextResolver;
        $this->localeResolver = $localeResolver;
        $this->skippedLogger = $skippedLogger;
        $this->config = $config;
    }

    /**
     * @return int
     */
    public function handle(): int
    {
        $modelOption = trim((string) $this->option('model'));
        $since = trim((string) $this->option('since')) ?: null;
        $this->dryRun = (bool) $this->option('dry-run');
        $this->limit = max(0, (int) $this->option('limit'));
        $this->prepared = 0;
        $this->scannedRows = 0;
        $this->debugRows = [];
        $this->hasLocaleColumn = Schema::hasTable((new ImageAltSuggestion())->getTable())
            && Schema::hasColumn((new ImageAltSuggestion())->getTable(), 'locale');

        $locales = $this->localesFromOption((string) $this->option('locale'));

        if (empty($locales)) {
            return 1;
        }

        $targets = (array) $this->config->get('alt_generation.targets', []);
        $modelClasses = $modelOption !== '' ? [$this->resolveModelOption($modelOption, $targets)] : array_keys($targets);
        $created = 0;

        foreach ($modelClasses as $modelClass) {
            if (!is_string($modelClass) || !class_exists($modelClass) || !is_subclass_of($modelClass, Model::class)) {
                $this->warn('Skipping invalid model: ' . (string) $modelClass);
                continue;
            }

            $target = $this->targetForModel($modelClass, $targets);

            if (isset($target['enabled']) && $target['enabled'] === false) {
                $this->warn('Skipping disabled alt-generation target: ' . $modelClass);
                continue;
            }

            $created += $this->scanModel($modelClass, $since, $locales);
            $created += $this->flushPendingRows();

            if ($this->limitReached()) {
                break;
            }
        }

        $created += $this->flushPendingRows();

        if (!empty($this->debugRows)) {
            $this->table(
                [
                    'model',
                    'id',
                    'field',
                    'source',
                    'collection',
                    'locale',
                    'image_path',
                    'page_url',
                    'resolver',
                    'action',
                ],
                array_slice($this->debugRows, 0, 50)
            );

            if (count($this->debugRows) > 50) {
                $this->line('Showing first 50 scan rows out of ' . count($this->debugRows) . '.');
            }
        }

        $this->info('Scanned image rows: ' . $this->scannedRows);
        $this->info(($this->dryRun ? 'Dry-run image alt suggestions: ' : 'Prepared image alt suggestions: ') . $created);

        return 0;
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     * @param string|null $since
     * @param array<int, string> $locales
     * @return int
     */
    private function scanModel(string $modelClass, ?string $since, array $locales): int
    {
        $created = 0;

        if (method_exists($modelClass, 'altScanEntities')) {
            /** @var iterable<int, \Illuminate\Database\Eloquent\Model> $entities */
            $entities = $modelClass::altScanEntities($since);

            Collection::make($entities)->chunk(200)->each(function (Collection $chunk) use (&$created, $locales): bool {
                foreach ($chunk as $entity) {
                    if ($entity instanceof Model && !$this->limitReached()) {
                        $created += $this->scanEntity($entity, $locales);
                    }

                    if ($this->limitReached()) {
                        return false;
                    }
                }

                return true;
            });

            return $created;
        }

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();
        $query = $modelClass::query()->orderBy($model->getKeyName());

        if ($since !== null && $this->hasColumn($model, 'updated_at')) {
            $query->where('updated_at', '>=', $since);
        }

        $query->chunk(200, function (Collection $entities) use (&$created, $locales): bool {
            foreach ($entities as $entity) {
                if ($entity instanceof Model && !$this->limitReached()) {
                    $created += $this->scanEntity($entity, $locales);
                }

                if ($this->limitReached()) {
                    return false;
                }
            }

            return true;
        });

        return $created;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param array<int, string> $locales
     * @return int
     */
    private function scanEntity(Model $entity, array $locales): int
    {
        $created = 0;

        foreach ($locales as $locale) {
            if ($this->limitReached()) {
                break;
            }

            $this->config->set('app.locale', $locale);
            app()->setLocale($locale);

            foreach ($this->collector->collect($entity, $locale) as $image) {
                if ($this->limitReached()) {
                    break;
                }

                if (!$image instanceof ImageDescriptor) {
                    continue;
                }

                $this->scannedRows++;

                $imageContext = $this->contextResolver->resolve($entity, $image, $locale);

                if (!$this->hasPageUrl($imageContext)) {
                    $this->skippedLogger->log($entity, 'no_public_url', [
                        'command' => 'alt:scan',
                        'locale' => $locale,
                        'field' => $image->field,
                        'image_path' => $image->path,
                        'resolver_source' => $imageContext['resolver_source'] ?? 'none',
                    ]);
                    $this->addDebugRow($entity, $image, $imageContext, $locale, 'skip:no_public_url');
                    continue;
                }

                $created += $this->prepareSuggestion($entity, $image, $imageContext, $locale);
            }
        }

        return $created;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @param array<string, mixed> $context
     * @param string $locale
     * @return int
     */
    private function prepareSuggestion(Model $entity, ImageDescriptor $image, array $context, string $locale): int
    {
        $hash = $this->imageHash($image);
        $existing = $this->suggestionIdentityQuery($entity, $image, $locale)->first();

        if ($existing instanceof ImageAltSuggestion) {
            return $this->refreshExistingSuggestion($existing, $image, $context, $hash, $locale);
        }

        if ($this->dryRun) {
            $this->addDebugRow($entity, $image, $context, $locale, 'create');
            $this->prepared++;

            return 1;
        }

        $now = Carbon::now();
        $row = [
            'imageable_type' => get_class($entity),
            'imageable_id' => $entity->getKey(),
            'field' => $image->field,
            'image_path' => $image->path,
            'page_url' => $this->pageUrlFromContext($context),
            'image_hash' => $hash,
            'current_alt' => $context['current_alt'] ?? $image->currentAlt,
            'current_title' => $context['current_title'] ?? $image->currentTitle,
            'status' => ImageAltSuggestion::STATUS_NEW,
            'prompt_context' => $this->jsonEncode($this->promptContext($context, $image)),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if ($this->hasLocaleColumn) {
            $row['locale'] = $locale;
        }

        $this->pendingRows[] = $row;
        $this->addDebugRow($entity, $image, $context, $locale, 'create');
        $this->prepared++;

        return count($this->pendingRows) >= self::INSERT_BATCH_SIZE
            ? $this->flushPendingRows()
            : 0;
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @param array<string, mixed> $context
     * @param string|null $hash
     * @param string $locale
     * @return int
     */
    private function refreshExistingSuggestion(
        ImageAltSuggestion $suggestion,
        ImageDescriptor $image,
        array $context,
        ?string $hash,
        string $locale
    ): int {
        if ((string) $suggestion->image_hash === (string) $hash) {
            return $this->refreshExistingContext($suggestion, $image, $context, $locale);
        }

        if ($this->dryRun) {
            $this->addDebugRowFromSuggestion($suggestion, $image, $context, $locale, 'reset');
            $this->prepared++;

            return 1;
        }

        $promptContext = $this->promptContext($context, $image);
        $promptContext['history'] = $this->historyWithSnapshot($suggestion, (array) $suggestion->prompt_context);

        $suggestion->fill([
            'image_hash' => $hash,
            'page_url' => $this->pageUrlFromContext($context),
            'current_alt' => $context['current_alt'] ?? $image->currentAlt,
            'current_title' => $context['current_title'] ?? $image->currentTitle,
            'suggested_alt' => null,
            'suggested_title' => null,
            'approved_alt' => null,
            'approved_title' => null,
            'prompt_context' => $promptContext,
            'model' => null,
            'tokens_used' => null,
            'error' => null,
            'generated_at' => null,
            'reviewed_at' => null,
            'applied_at' => null,
            'reviewed_by' => null,
        ]);

        if ($this->hasLocaleColumn) {
            $suggestion->locale = $locale;
        }

        $suggestion->setStatus(ImageAltSuggestion::STATUS_NEW);
        $suggestion->save();

        return 1;
    }

    /**
     * Refresh audit context without resetting review/generation state.
     *
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @param array<string, mixed> $context
     * @param string $locale
     * @return int
     */
    private function refreshExistingContext(
        ImageAltSuggestion $suggestion,
        ImageDescriptor $image,
        array $context,
        string $locale
    ): int {
        $pageUrl = $this->pageUrlFromContext($context);
        $currentAlt = $context['current_alt'] ?? $image->currentAlt;
        $currentTitle = $context['current_title'] ?? $image->currentTitle;
        $promptContext = $this->promptContext($context, $image);

        $changed = (string) $suggestion->page_url !== (string) $pageUrl
            || (string) $suggestion->current_alt !== (string) $currentAlt
            || (string) $suggestion->current_title !== (string) $currentTitle
            || json_encode($suggestion->prompt_context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                !== json_encode($promptContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (!$changed) {
            return 0;
        }

        if ($this->dryRun) {
            $this->addDebugRowFromSuggestion($suggestion, $image, $context, $locale, 'update_context');
            $this->prepared++;

            return 1;
        }

        $suggestion->page_url = $pageUrl;
        $suggestion->current_alt = $currentAlt;
        $suggestion->current_title = $currentTitle;
        $suggestion->prompt_context = $promptContext;

        if ($this->hasLocaleColumn) {
            $suggestion->locale = $locale;
        }

        $suggestion->save();

        return 1;
    }

    /**
     * @return int
     */
    private function flushPendingRows(): int
    {
        if (empty($this->pendingRows)) {
            return 0;
        }

        $rows = $this->pendingRows;
        $this->pendingRows = [];

        return (int) DB::table((new ImageAltSuggestion())->getTable())->insertOrIgnore($rows);
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param array<string, mixed> $promptContext
     * @return array<int, array<string, mixed>>
     */
    private function historyWithSnapshot(ImageAltSuggestion $suggestion, array $promptContext): array
    {
        $history = isset($promptContext['history']) && is_array($promptContext['history'])
            ? $promptContext['history']
            : [];

        $history[] = [
            'changed_at' => Carbon::now()->toDateTimeString(),
            'status' => $suggestion->status,
            'locale' => $suggestion->locale,
            'image_hash' => $suggestion->image_hash,
            'page_url' => $suggestion->page_url,
            'current_alt' => $suggestion->current_alt,
            'current_title' => $suggestion->current_title,
            'suggested_alt' => $suggestion->suggested_alt,
            'suggested_title' => $suggestion->suggested_title,
            'approved_alt' => $suggestion->approved_alt,
            'approved_title' => $suggestion->approved_title,
            'model' => $suggestion->model,
            'tokens_used' => $suggestion->tokens_used,
            'generated_at' => $suggestion->generated_at ? $suggestion->generated_at->toDateTimeString() : null,
            'reviewed_at' => $suggestion->reviewed_at ? $suggestion->reviewed_at->toDateTimeString() : null,
            'applied_at' => $suggestion->applied_at ? $suggestion->applied_at->toDateTimeString() : null,
            'context' => $promptContext['context'] ?? null,
            'image' => $promptContext['image'] ?? null,
        ];

        return $history;
    }

    /**
     * @param array<string, mixed> $context
     * @return bool
     */
    private function hasPageUrl(array $context): bool
    {
        return $this->pageUrlFromContext($context) !== null;
    }

    /**
     * @param array<string, mixed> $context
     * @return string|null
     */
    private function pageUrlFromContext(array $context): ?string
    {
        $pageUrl = $context['page_url'] ?? ($context['url'] ?? null);

        return $pageUrl === null || trim((string) $pageUrl) === '' ? null : (string) $pageUrl;
    }

    /**
     * @param array<string, mixed> $context
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @return array<string, mixed>
     */
    private function promptContext(array $context, ImageDescriptor $image): array
    {
        return [
            'context' => $context,
            'image' => $image->toArray(),
            'limits' => $this->config->get('alt_generation.limits', []),
            'instructions' => [
                'Do not repeat wording from sibling_image_alts.',
                'Language MUST match language.',
                'Alt <= 125 chars, Title <= 70 chars.',
                'Describe what is actually visible.',
                'No keyword stuffing.',
                'Mention brand only if visible or contextually required.',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $value
     * @return string
     */
    private function jsonEncode(array $value): string
    {
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $encoded === false ? '{}' : $encoded;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @param string $locale
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function suggestionIdentityQuery(Model $entity, ImageDescriptor $image, string $locale)
    {
        $query = ImageAltSuggestion::query()
            ->where('imageable_type', get_class($entity))
            ->where('imageable_id', $entity->getKey())
            ->where('image_path', $image->path);

        $image->field === null
            ? $query->whereNull('field')
            : $query->where('field', $image->field);

        if ($this->hasLocaleColumn) {
            $query->where('locale', $locale);
        }

        return $query;
    }

    /**
     * @param string $locale
     * @return array<int, string>
     */
    private function localesFromOption(string $locale): array
    {
        $locale = trim($locale);

        if ($locale === '') {
            return $this->localeResolver->supportedLocales();
        }

        if (!$this->localeResolver->isSupported($locale)) {
            $this->error('Unsupported locale for alt generation: ' . $locale);
            $this->line('Supported locales: ' . implode(', ', $this->localeResolver->supportedLocales()));

            return [];
        }

        return [$this->localeResolver->normalizeOrDefault($locale)];
    }

    /**
     * @param string $model
     * @param array<string, mixed> $targets
     * @return string
     */
    private function resolveModelOption(string $model, array $targets): string
    {
        if (class_exists($model)) {
            return $model;
        }

        foreach (array_keys($targets) as $class) {
            if (is_string($class) && class_basename($class) === $model) {
                return $class;
            }
        }

        return $model;
    }

    /**
     * @return bool
     */
    private function limitReached(): bool
    {
        return $this->limit > 0 && $this->scannedRows >= $this->limit;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @param array<string, mixed> $context
     * @param string $locale
     * @param string $action
     * @return void
     */
    private function addDebugRow(
        Model $entity,
        ImageDescriptor $image,
        array $context,
        string $locale,
        string $action
    ): void {
        $this->debugRows[] = [
            class_basename(get_class($entity)),
            (string) $entity->getKey(),
            (string) $image->field,
            $image->sourceType,
            (string) ($image->meta['collection_name'] ?? ''),
            $locale,
            $image->path,
            (string) $this->pageUrlFromContext($context),
            (string) ($context['resolver_source'] ?? ''),
            $action,
        ];
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @param array<string, mixed> $context
     * @param string $locale
     * @param string $action
     * @return void
     */
    private function addDebugRowFromSuggestion(
        ImageAltSuggestion $suggestion,
        ImageDescriptor $image,
        array $context,
        string $locale,
        string $action
    ): void {
        $this->debugRows[] = [
            class_basename((string) $suggestion->imageable_type),
            (string) $suggestion->imageable_id,
            (string) $image->field,
            $image->sourceType,
            (string) ($image->meta['collection_name'] ?? ''),
            $locale,
            $image->path,
            (string) $this->pageUrlFromContext($context),
            (string) ($context['resolver_source'] ?? ''),
            $action,
        ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $column
     * @return bool
     */
    private function hasColumn(Model $model, string $column): bool
    {
        return $model->getConnection()
            ->getSchemaBuilder()
            ->hasColumn($model->getTable(), $column);
    }

    /**
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @return string|null
     */
    private function imageHash(ImageDescriptor $image): ?string
    {
        if ($image->absolutePath === null || !is_file($image->absolutePath)) {
            return null;
        }

        $hash = hash_file('sha256', $image->absolutePath);

        return $hash === false ? null : $hash;
    }

    /**
     * @param string $modelClass
     * @param array<string, mixed> $targets
     * @return array<string, mixed>
     */
    private function targetForModel(string $modelClass, array $targets): array
    {
        if (isset($targets[$modelClass]) && is_array($targets[$modelClass])) {
            return $targets[$modelClass];
        }

        foreach ($targets as $class => $target) {
            if (is_string($class) && is_subclass_of($modelClass, $class) && is_array($target)) {
                return $target;
            }
        }

        return [];
    }
}
