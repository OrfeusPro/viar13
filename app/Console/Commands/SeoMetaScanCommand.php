<?php

namespace App\Console\Commands;

use App\Models\SeoMetaSuggestion;
use App\Services\SeoMetaGeneration\SeoMetaContextResolver;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;

class SeoMetaScanCommand extends Command
{
    protected $signature = 'seo-meta:scan
        {--model= : Supported model FQCN or class basename}
        {--locale= : Scan one locale}
        {--locales= : Scan selected comma-separated locales}
        {--only-empty : Include only entities with empty meta title or meta description}
        {--limit=1000 : Max entities per model}
        {--force : Refresh existing suggestions from current entity values}';

    protected $description = 'Create moderation queue records for SEO meta title/description generation';

    /**
     * @var \App\Services\SeoMetaGeneration\SeoMetaContextResolver
     */
    private $contextResolver;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    public function __construct(SeoMetaContextResolver $contextResolver, ConfigRepository $config)
    {
        parent::__construct();

        $this->contextResolver = $contextResolver;
        $this->config = $config;
    }

    public function handle(): int
    {
        $targets = $this->targetsToScan();
        $locales = $this->requestedLocales();
        $limit = max(1, (int) $this->option('limit'));
        $onlyEmpty = (bool) $this->option('only-empty');
        $force = (bool) $this->option('force');
        $created = 0;
        $updated = 0;
        $skipped = 0;

        if (empty($targets)) {
            $this->error('No supported SEO meta targets were selected.');

            return 1;
        }

        if (empty($locales)) {
            $this->error('No supported locales were selected.');
            $this->line('Supported locales: ' . implode(', ', $this->contextResolver->supportedLocales()));

            return 1;
        }

        foreach ($targets as $modelClass => $target) {
            if (!class_exists($modelClass) || !is_subclass_of($modelClass, Model::class)) {
                $this->warn('Skipped invalid model: ' . $modelClass);
                continue;
            }

            /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
            $this->line('Scanning ' . class_basename($modelClass) . '...');

            $entities = $modelClass::query()
                ->orderBy((new $modelClass())->getKeyName())
                ->limit($limit)
                ->get();

            foreach ($entities as $entity) {
                if (!$entity instanceof Model) {
                    continue;
                }

                foreach ($locales as $locale) {
                    $snapshot = $this->snapshot($entity, $target, $locale);

                    if ($onlyEmpty && $snapshot['has_title'] && $snapshot['has_description']) {
                        $skipped++;
                        continue;
                    }

                    $identity = [
                        'metaable_type' => $modelClass,
                        'metaable_id' => $entity->getKey(),
                        'locale' => $locale,
                    ];
                    $suggestion = SeoMetaSuggestion::query()->firstOrNew($identity);
                    $isNew = !$suggestion->exists;

                    if (!$isNew && !$force) {
                        $skipped++;
                        continue;
                    }

                    $suggestion->fill(array_merge($identity, [
                        'page_url' => $snapshot['page_url'],
                        'entity_label' => (string) ($target['label'] ?? class_basename($entity)),
                        'entity_title' => $snapshot['entity_title'],
                        'title_field' => (string) $target['title_field'],
                        'description_field' => (string) $target['description_field'],
                        'current_meta_title' => $snapshot['current_meta_title'],
                        'current_meta_description' => $snapshot['current_meta_description'],
                        'prompt_context' => [
                            'context' => $snapshot['context'],
                            'limits' => $this->config->get('seo_meta_generation.limits', []),
                        ],
                    ]));

                    if ($isNew || trim((string) $suggestion->status) === '') {
                        $suggestion->setStatus(SeoMetaSuggestion::STATUS_NEW);
                    }

                    $suggestion->save();

                    if ($isNew) {
                        $created++;
                    } else {
                        $updated++;
                    }
                }
            }
        }

        $this->info('SEO meta scan completed. Created: ' . $created . ', updated: ' . $updated . ', skipped: ' . $skipped . '.');

        return 0;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function targetsToScan(): array
    {
        $targets = (array) $this->config->get('seo_meta_generation.targets', []);
        $model = trim((string) $this->option('model'));

        if ($model === '') {
            return $targets;
        }

        $resolved = $this->resolveModelOption($model, $targets);

        return isset($targets[$resolved]) && is_array($targets[$resolved]) ? [$resolved => $targets[$resolved]] : [];
    }

    /**
     * @param array<string, mixed> $targets
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
     * @return array<int, string>
     */
    private function requestedLocales(): array
    {
        $locales = [];
        $locale = trim((string) $this->option('locale'));
        $localesOption = trim((string) $this->option('locales'));

        if ($locale !== '') {
            $locales[] = $locale;
        }

        if ($localesOption !== '') {
            $locales = array_merge($locales, array_map('trim', explode(',', $localesOption)));
        }

        if (empty($locales)) {
            $locales = $this->contextResolver->supportedLocales();
        }

        return $this->contextResolver->normalizeRequestedLocales($locales);
    }

    /**
     * @param array<string, mixed> $target
     * @return array<string, mixed>
     */
    private function snapshot(Model $entity, array $target, string $locale): array
    {
        $context = $this->contextResolver->resolve($entity, [$locale]);
        $localeContext = isset($context['locales'][$locale]) && is_array($context['locales'][$locale])
            ? $context['locales'][$locale]
            : [];
        $entityContext = isset($context['entity']) && is_array($context['entity']) ? $context['entity'] : [];
        $currentTitle = $this->readTranslatedValue($entity, (string) $target['title_field'], $locale);
        $currentDescription = $this->readTranslatedValue($entity, (string) $target['description_field'], $locale);

        return [
            'context' => $context,
            'page_url' => isset($localeContext['page_url']) ? (string) $localeContext['page_url'] : (isset($entityContext['url']) ? (string) $entityContext['url'] : null),
            'entity_title' => isset($localeContext['source_title']) ? (string) $localeContext['source_title'] : (isset($localeContext['current_meta_title']) ? (string) $localeContext['current_meta_title'] : null),
            'current_meta_title' => $currentTitle,
            'current_meta_description' => $currentDescription,
            'has_title' => $currentTitle !== null && trim($currentTitle) !== '',
            'has_description' => $currentDescription !== null && trim($currentDescription) !== '',
        ];
    }

    private function readTranslatedValue(Model $entity, string $field, string $locale): ?string
    {
        if (method_exists($entity, 'getTranslatedAttribute')) {
            try {
                $value = $entity->getTranslatedAttribute($field, $locale, false);

                return $value === null ? null : (string) $value;
            } catch (\Throwable $exception) {
                // Fall back to base attribute below.
            }
        }

        if (!array_key_exists($field, $entity->getAttributes())) {
            return null;
        }

        $value = $entity->getAttribute($field);

        return $value === null ? null : (string) $value;
    }
}
