<?php

namespace App\Console\Commands;

use App\Services\SeoMetaGeneration\SeoMetaContextResolver;
use App\Services\SeoMetaGeneration\SeoMetaGenerator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;

class SeoMetaGenerateCommand extends Command
{
    protected $signature = 'seo-meta:generate
        {--model= : Supported model FQCN or class basename}
        {--id= : Entity id}
        {--dry-run : Print compressed context and generated result, do not save}
        {--apply : Save generated values}
        {--force : Overwrite already-filled fields during apply}
        {--locale= : Generate one locale}
        {--locales= : Generate selected comma-separated locales}
        {--no-cache : Bypass cached generation results}';

    protected $description = 'Generate multilingual SEO meta title/description for supported Voyager entities';

    /**
     * @var \App\Services\SeoMetaGeneration\SeoMetaGenerator
     */
    private $generator;

    /**
     * @var \App\Services\SeoMetaGeneration\SeoMetaContextResolver
     */
    private $contextResolver;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    public function __construct(
        SeoMetaGenerator $generator,
        SeoMetaContextResolver $contextResolver,
        ConfigRepository $config
    ) {
        parent::__construct();

        $this->generator = $generator;
        $this->contextResolver = $contextResolver;
        $this->config = $config;
    }

    public function handle(): int
    {
        $modelClass = $this->resolveModelOption(trim((string) $this->option('model')));
        $id = trim((string) $this->option('id'));

        if ($modelClass === '') {
            $this->error('--model is required.');

            return 1;
        }

        if ($id === '') {
            $this->error('--id is required.');

            return 1;
        }

        if ($this->contextResolver->targetForModel($modelClass) === null) {
            $this->error('Unsupported model for SEO meta generation: ' . $modelClass);

            return 1;
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        $entity = $modelClass::query()->find($id);

        if (!$entity instanceof Model) {
            $this->error('Entity not found: ' . class_basename($modelClass) . '#' . $id);

            return 1;
        }

        $target = $this->contextResolver->targetForModel($modelClass);
        $requestedLocales = $this->requestedLocales();

        if (empty($requestedLocales)) {
            $this->error('No supported locales were requested.');
            $this->line('Supported locales: ' . implode(', ', $this->contextResolver->supportedLocales()));

            return 1;
        }

        $dryRun = (bool) $this->option('dry-run');
        $apply = (bool) $this->option('apply') && !$dryRun;
        $force = (bool) $this->option('force');
        $localesToGenerate = $apply && !$force
            ? $this->localesNeedingValues($entity, $target, $requestedLocales)
            : $requestedLocales;

        if ($apply && !$force && empty($localesToGenerate)) {
            $this->info('All requested SEO meta fields are already filled; skipping OpenAI call. Use --force to overwrite.');

            return 0;
        }

        $result = $this->generator->generate($entity, $localesToGenerate, !(bool) $this->option('no-cache'));

        if ($dryRun) {
            $this->line('Context:');
            $this->line($result['context_json']);
            $this->line('Generated:');
            $this->line((string) json_encode($result['locales'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        } else {
            $this->line((string) json_encode([
                'cached' => $result['cached'],
                'locales' => $result['locales'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }

        if (!$apply) {
            $this->info('Generated SEO meta was not saved. Pass --apply to save.');

            return 0;
        }

        $written = $this->applyResult($entity, $target, $result['locales'], $force);
        $this->info('Saved SEO meta fields: ' . $written);

        return 0;
    }

    private function resolveModelOption(string $model): string
    {
        if ($model === '') {
            return '';
        }

        if (class_exists($model)) {
            return $model;
        }

        foreach (array_keys((array) $this->config->get('seo_meta_generation.targets', [])) as $class) {
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
     * @param array<int, string> $locales
     * @return array<int, string>
     */
    private function localesNeedingValues(Model $entity, array $target, array $locales): array
    {
        $needed = [];

        foreach ($locales as $locale) {
            if (
                !$this->hasValue($entity, (string) $target['title_field'], $locale)
                || !$this->hasValue($entity, (string) $target['description_field'], $locale)
            ) {
                $needed[] = $locale;
            }
        }

        return $needed;
    }

    private function hasValue(Model $entity, string $field, string $locale): bool
    {
        $value = $this->readTranslatedValue($entity, $field, $locale);

        return $value !== null && trim($value) !== '';
    }

    /**
     * @param array<string, mixed> $target
     * @param array<string, array{meta_title: string, meta_description: string}> $locales
     */
    private function applyResult(Model $entity, array $target, array $locales, bool $force): int
    {
        $written = 0;
        $titleField = (string) $target['title_field'];
        $descriptionField = (string) $target['description_field'];

        foreach ($locales as $locale => $values) {
            if (!isset($values['meta_title'], $values['meta_description'])) {
                continue;
            }

            if (trim((string) $values['meta_title']) !== '' && ($force || !$this->hasValue($entity, $titleField, $locale))) {
                $this->writeTranslatedValue($entity, $titleField, (string) $values['meta_title'], $locale);
                $written++;
            }

            if (trim((string) $values['meta_description']) !== '' && ($force || !$this->hasValue($entity, $descriptionField, $locale))) {
                $this->writeTranslatedValue($entity, $descriptionField, (string) $values['meta_description'], $locale);
                $written++;
            }
        }

        return $written;
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

        $value = $entity->getAttribute($field);

        return $value === null ? null : (string) $value;
    }

    private function writeTranslatedValue(Model $entity, string $field, string $value, string $locale): void
    {
        if (method_exists($entity, 'setAttributeTranslations')) {
            $entity->setAttributeTranslations($field, [$locale => $value], true);
            $entity->save();
            $entity->load('translations');

            return;
        }

        $entity->setAttribute($field, $value);
        $entity->save();
    }
}
