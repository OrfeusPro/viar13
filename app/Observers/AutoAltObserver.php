<?php

namespace App\Observers;

use App\Jobs\GenerateImageAltJob;
use App\Models\ImageAltSuggestion;
use App\Services\AltGeneration\ImageCollector;
use App\Services\AltGeneration\ImageDescriptor;
use App\Services\AltGeneration\LocaleResolver;
use App\Services\AltGeneration\PageContextResolver;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Dispatches alt-generation work when configured model image fields change.
 */
class AutoAltObserver
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \App\Services\AltGeneration\ImageCollector
     */
    private $collector;

    /**
     * @var \Illuminate\Contracts\Bus\Dispatcher
     */
    private $dispatcher;

    /**
     * @var \App\Services\AltGeneration\PageContextResolver
     */
    private $contextResolver;

    /**
     * @var \App\Services\AltGeneration\LocaleResolver
     */
    private $localeResolver;

    /**
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \App\Services\AltGeneration\ImageCollector $collector
     * @param \App\Services\AltGeneration\PageContextResolver $contextResolver
     * @param \App\Services\AltGeneration\LocaleResolver $localeResolver
     * @param \Illuminate\Contracts\Bus\Dispatcher $dispatcher
     */
    public function __construct(
        ConfigRepository $config,
        ImageCollector $collector,
        PageContextResolver $contextResolver,
        LocaleResolver $localeResolver,
        Dispatcher $dispatcher
    ) {
        $this->config = $config;
        $this->collector = $collector;
        $this->contextResolver = $contextResolver;
        $this->localeResolver = $localeResolver;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @return void
     */
    public function created(Model $entity): void
    {
        if (!$this->observerEnabled()) {
            return;
        }

        $this->dispatchForChangedImages($entity, null, $this->configuredFields($entity));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @return void
     */
    public function updated(Model $entity): void
    {
        if (!$this->observerEnabled()) {
            return;
        }

        $fields = $this->changedConfiguredFields($entity);

        if (empty($fields)) {
            return;
        }

        $original = $entity->newInstance([], true);
        $original->setRawAttributes($entity->getOriginal(), true);

        $this->dispatchForChangedImages($entity, $original, $fields);
    }

    /**
     * @return bool
     */
    private function observerEnabled(): bool
    {
        return (bool) $this->config->get('alt_generation.auto_observer', false);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \Illuminate\Database\Eloquent\Model|null $original
     * @param array<int, string> $fields
     * @return void
     */
    private function dispatchForChangedImages(Model $entity, ?Model $original, array $fields): void
    {
        if ($entity->getKey() === null || !$this->suggestionsTableExists($entity)) {
            return;
        }

        foreach ($this->localeResolver->supportedLocales() as $locale) {
            $this->config->set('app.locale', $locale);
            app()->setLocale($locale);
            $context = $this->contextResolver->resolve($entity, null, $locale);
            $pageUrl = $this->pageUrlFromContext($context);

            if ($pageUrl === null) {
                continue;
            }

            $oldKeys = $original ? $this->descriptorKeys($this->collector->collect($original, $locale), $fields) : [];

            foreach ($this->collector->collect($entity, $locale) as $descriptor) {
                if (!$descriptor instanceof ImageDescriptor || !in_array((string) $descriptor->field, $fields, true)) {
                    continue;
                }

                $key = $this->descriptorKey($descriptor);

                if (isset($oldKeys[$key]) && $this->shouldSkipExisting($entity, $descriptor, $locale)) {
                    continue;
                }

                if ($this->shouldSkipExisting($entity, $descriptor, $locale)) {
                    continue;
                }

                $imageContext = $this->contextResolver->resolve($entity, $descriptor, $locale);
                $suggestion = $this->createSuggestion($entity, $descriptor, $imageContext, $locale);

                $this->dispatcher->dispatch(new GenerateImageAltJob((int) $suggestion->id));
            }
        }
    }

    /**
     * @param \Illuminate\Support\Collection<int, \App\Services\AltGeneration\ImageDescriptor> $descriptors
     * @param array<int, string> $fields
     * @return array<string, bool>
     */
    private function descriptorKeys(Collection $descriptors, array $fields): array
    {
        $keys = [];

        foreach ($descriptors as $descriptor) {
            if ($descriptor instanceof ImageDescriptor && in_array((string) $descriptor->field, $fields, true)) {
                $keys[$this->descriptorKey($descriptor)] = true;
            }
        }

        return $keys;
    }

    /**
     * @param \App\Services\AltGeneration\ImageDescriptor $descriptor
     * @return string
     */
    private function descriptorKey(ImageDescriptor $descriptor): string
    {
        return (string) $descriptor->field . '|' . $descriptor->path;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Services\AltGeneration\ImageDescriptor $descriptor
     * @return bool
     */
    private function shouldSkipExisting(Model $entity, ImageDescriptor $descriptor, string $locale): bool
    {
        $query = ImageAltSuggestion::query()
            ->where('imageable_type', get_class($entity))
            ->where('imageable_id', $entity->getKey())
            ->where('image_path', $descriptor->path)
            ->whereIn('status', ['pending', 'approved']);

        $descriptor->field === null
            ? $query->whereNull('field')
            : $query->where('field', $descriptor->field);

        if ($this->suggestionsHasColumn('locale')) {
            $query->where('locale', $locale);
        }

        $existing = $query->first();

        if (!$existing) {
            return false;
        }

        $hash = $this->imageHash($descriptor);

        return $hash === null || $existing->image_hash === $hash;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Services\AltGeneration\ImageDescriptor $descriptor
     * @param array<string, mixed> $context
     * @param string $locale
     * @return \App\Models\ImageAltSuggestion
     */
    private function createSuggestion(
        Model $entity,
        ImageDescriptor $descriptor,
        array $context,
        string $locale
    ): ImageAltSuggestion {
        $identity = [
            'imageable_type' => get_class($entity),
            'imageable_id' => $entity->getKey(),
            'image_path' => $descriptor->path,
            'field' => $descriptor->field,
        ];

        if ($this->suggestionsHasColumn('locale')) {
            $identity['locale'] = $locale;
        }

        $values = [
            'page_url' => $this->pageUrlFromContext($context),
            'image_hash' => $this->imageHash($descriptor),
            'current_alt' => $context['current_alt'] ?? $descriptor->currentAlt,
            'current_title' => $context['current_title'] ?? $descriptor->currentTitle,
            'status' => ImageAltSuggestion::STATUS_NEW,
            'prompt_context' => [
                'context' => $context,
                'image' => $descriptor->toArray(),
            ],
        ];

        if ($this->suggestionsHasColumn('locale')) {
            $values['locale'] = $locale;
        }

        return ImageAltSuggestion::firstOrCreate($identity, $values);
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
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @return array<int, string>
     */
    private function changedConfiguredFields(Model $entity): array
    {
        $changed = [];
        $changes = method_exists($entity, 'getChanges') ? $entity->getChanges() : [];

        foreach ($this->configuredFields($entity) as $field) {
            if (!array_key_exists($field, $entity->getAttributes())) {
                continue;
            }

            if (
                array_key_exists($field, $changes)
                || (string) $entity->getOriginal($field) !== (string) $entity->getAttribute($field)
            ) {
                $changed[] = $field;
            }
        }

        return $changed;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @return array<int, string>
     */
    private function configuredFields(Model $entity): array
    {
        $target = (array) $this->config->get('alt_generation.targets.' . get_class($entity), []);

        return array_values(array_unique(array_filter(array_merge(
            (array) ($target['image_fields'] ?? []),
            (array) ($target['gallery_fields'] ?? []),
            (array) ($target['html_fields'] ?? [])
        ), 'is_string')));
    }

    /**
     * @param \App\Services\AltGeneration\ImageDescriptor $descriptor
     * @return string|null
     */
    private function imageHash(ImageDescriptor $descriptor): ?string
    {
        if ($descriptor->absolutePath === null || !is_file($descriptor->absolutePath)) {
            return null;
        }

        $hash = hash_file('sha256', $descriptor->absolutePath);

        return $hash === false ? null : $hash;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @return bool
     */
    private function suggestionsTableExists(Model $entity): bool
    {
        return $entity->getConnection()
            ->getSchemaBuilder()
            ->hasTable((new ImageAltSuggestion())->getTable());
    }

    /**
     * @param string $column
     * @return bool
     */
    private function suggestionsHasColumn(string $column): bool
    {
        $model = new ImageAltSuggestion();

        return $model->getConnection()
            ->getSchemaBuilder()
            ->hasColumn($model->getTable(), $column);
    }
}
