<?php

namespace App\Console\Commands;

use App\Models\ImageAltSuggestion;
use App\Services\AltGeneration\LocaleResolver;
use App\Services\AltGeneration\PageContextResolver;
use App\Services\AltGeneration\SkippedAltGenerationLogger;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;
use App\Services\AltGeneration\ImageDescriptor;

/**
 * Backfills public page URLs on existing image alt suggestions.
 */
class AltBackfillUrlsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'alt:backfill-urls
        {--model= : Limit to one imageable model FQCN or class basename}
        {--locale= : Limit backfill to one supported locale}
        {--limit=1000 : Maximum suggestions to process}
        {--all : Re-resolve rows that already have page_url}
        {--dry-run : Print updates without saving}';

    /**
     * @var string
     */
    protected $description = 'Backfill page_url on image alt suggestions from configured public URL resolvers';

    /**
     * @var \App\Services\AltGeneration\PageContextResolver
     */
    private $contextResolver;

    /**
     * @var \App\Services\AltGeneration\LocaleResolver
     */
    private $localeResolver;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \App\Services\AltGeneration\SkippedAltGenerationLogger
     */
    private $skippedLogger;

    /**
     * @param \App\Services\AltGeneration\PageContextResolver $contextResolver
     * @param \App\Services\AltGeneration\LocaleResolver $localeResolver
     * @param \App\Services\AltGeneration\SkippedAltGenerationLogger $skippedLogger
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(
        PageContextResolver $contextResolver,
        LocaleResolver $localeResolver,
        SkippedAltGenerationLogger $skippedLogger,
        ConfigRepository $config
    ) {
        parent::__construct();

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
        $model = $this->resolveModelOption(trim((string) $this->option('model')));
        $locale = trim((string) $this->option('locale'));
        $limit = max(0, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        if ($locale !== '' && !$this->localeResolver->isSupported($locale)) {
            $this->error('Unsupported locale for alt generation: ' . $locale);
            $this->line('Supported locales: ' . implode(', ', $this->localeResolver->supportedLocales()));

            return 1;
        }

        $locale = $locale === '' ? null : $this->localeResolver->normalizeOrDefault($locale);

        $query = ImageAltSuggestion::query()
            ->whereNotNull('imageable_type')
            ->whereNotNull('imageable_id')
            ->orderBy('id');

        if (!(bool) $this->option('all')) {
            $query->whereNull('page_url');
        }

        if ($model !== '') {
            $query->where('imageable_type', $model);
        }

        if ($locale !== null) {
            $query->where('locale', $locale);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        /** @var \Illuminate\Support\Collection<int, \App\Models\ImageAltSuggestion> $suggestions */
        $suggestions = $query->get();
        $bar = $this->output->createProgressBar($suggestions->count());
        $bar->start();

        $updated = 0;
        $skipped = 0;

        foreach ($suggestions as $suggestion) {
            $result = $this->backfillSuggestion($suggestion, $dryRun);

            if ($result === true) {
                $updated++;
            } else {
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->info('Backfilled page URLs: ' . $updated);
        $this->info('Skipped suggestions: ' . $skipped);

        return 0;
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param bool $dryRun
     * @return bool
     */
    private function backfillSuggestion(ImageAltSuggestion $suggestion, bool $dryRun): bool
    {
        $entity = $this->resolveEntity($suggestion);

        if (!$entity) {
            $this->warn('Skipping suggestion #' . $suggestion->id . ': imageable entity was not found');

            return false;
        }

        $locale = $this->localeResolver->normalizeOrDefault($suggestion->locale);
        $this->config->set('app.locale', $locale);
        app()->setLocale($locale);
        $image = $this->imageDescriptorFromSuggestion($suggestion);

        $context = $this->contextResolver->resolve($entity, $image, $locale);
        $url = $this->pageUrlFromContext($context);

        if ($url === null) {
            $this->skippedLogger->log($entity, 'no_public_url', [
                'command' => 'alt:backfill-urls',
                'suggestion_id' => $suggestion->id,
                'locale' => $locale,
                'image_path' => $suggestion->image_path,
                'field' => $suggestion->field,
                'resolver_source' => $context['resolver_source'] ?? 'none',
            ]);

            if ($dryRun) {
                $this->line('');
                $this->line(
                    'Suggestion #' . $suggestion->id
                    . ' [' . class_basename((string) $suggestion->imageable_type)
                    . ' #' . $suggestion->imageable_id
                    . ' ' . (string) $suggestion->field
                    . ' ' . $locale
                    . '] page_url would be cleared'
                );

                return true;
            }

            $promptContext = (array) $suggestion->prompt_context;
            $promptContext['context'] = $context;

            $suggestion->page_url = null;
            $suggestion->prompt_context = $promptContext;
            $suggestion->save();

            return true;
        }

        if ($dryRun) {
            $this->line('');
            $this->line(
                'Suggestion #' . $suggestion->id
                . ' [' . class_basename((string) $suggestion->imageable_type)
                . ' #' . $suggestion->imageable_id
                . ' ' . (string) $suggestion->field
                . ' ' . $locale
                . '] page_url: ' . $url
            );

            return true;
        }

        $promptContext = (array) $suggestion->prompt_context;
        $promptContext['context'] = $context;

        $suggestion->page_url = $url;
        $suggestion->prompt_context = $promptContext;
        $suggestion->save();

        return true;
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
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return \App\Services\AltGeneration\ImageDescriptor
     */
    private function imageDescriptorFromSuggestion(ImageAltSuggestion $suggestion): ImageDescriptor
    {
        $promptContext = (array) $suggestion->prompt_context;
        $image = isset($promptContext['image']) && is_array($promptContext['image'])
            ? $promptContext['image']
            : [];

        return new ImageDescriptor(
            (string) $suggestion->image_path,
            $suggestion->field,
            $suggestion->current_alt,
            $suggestion->current_title,
            isset($image['source_type']) && is_string($image['source_type']) ? $image['source_type'] : 'field',
            isset($image['xpath']) && is_string($image['xpath']) ? $image['xpath'] : null,
            null,
            isset($image['public_url']) && is_string($image['public_url']) ? $image['public_url'] : null,
            $image
        );
    }
}
