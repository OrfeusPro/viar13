<?php

namespace App\Services\AltGeneration;

use App\Models\ImageAltSuggestion;
use ArrayAccess;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Resolves rendered alt/title attributes for generated image metadata.
 */
class AltAttributeResolver
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \App\Services\AltGeneration\LocaleResolver
     */
    private $localeResolver;

    /**
     * @var array<string, \App\Models\ImageAltSuggestion|null>
     */
    private $suggestionCache = [];

    /**
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \App\Services\AltGeneration\LocaleResolver $localeResolver
     */
    public function __construct(ConfigRepository $config, LocaleResolver $localeResolver)
    {
        $this->config = $config;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Build escaped HTML alt/title attributes for a model image field.
     *
     * @param mixed $model
     * @param string $field
     * @param string|null $imagePath
     * @param string|null $locale
     * @param string|null $fallbackAlt
     * @param string|null $fallbackTitle
     * @return string
     */
    public function attributesFor(
        $model,
        string $field,
        ?string $imagePath = null,
        ?string $locale = null,
        ?string $fallbackAlt = null,
        ?string $fallbackTitle = null
    ): string {
        $values = $this->valuesFor($model, $field, $imagePath, $locale, $fallbackAlt, $fallbackTitle);
        $attributes = 'alt="' . e((string) ($values['alt'] ?? '')) . '"';

        if (($values['title'] ?? null) !== null && trim((string) $values['title']) !== '') {
            $attributes .= ' title="' . e((string) $values['title']) . '"';
        }

        return $attributes;
    }

    /**
     * Resolve raw alt/title values for a model image field.
     *
     * @param mixed $model
     * @param string $field
     * @param string|null $imagePath
     * @param string|null $locale
     * @param string|null $fallbackAlt
     * @param string|null $fallbackTitle
     * @return array{alt: string|null, title: string|null}
     */
    public function valuesFor(
        $model,
        string $field,
        ?string $imagePath = null,
        ?string $locale = null,
        ?string $fallbackAlt = null,
        ?string $fallbackTitle = null
    ): array {
        $target = $this->targetForModel($model);
        $map = $this->applyMap($model, $target, $field);
        $resolvedImagePath = $imagePath !== null ? $imagePath : $this->inferImagePath($model, $field);
        $identity = $this->identityFor($model);
        $locale = $locale === null
            ? $this->localeResolver->currentLocale()
            : $this->localeResolver->normalizeOrDefault($locale);
        $canUseSuggestionLookup = !$this->requiresExactImagePath($target, $field)
            || $this->normalizeImagePath($resolvedImagePath) !== null;

        $alt = null;
        $title = null;

        if (($alt === null || $title === null) && $identity !== null && $canUseSuggestionLookup) {
            $suggestion = $this->appliedSuggestion($model, $field, $locale, $resolvedImagePath);

            if ($suggestion !== null) {
                if ($alt === null) {
                    $alt = $suggestion->approved_alt !== null
                        ? $suggestion->approved_alt
                        : $suggestion->suggested_alt;
                }

                if ($title === null) {
                    $title = $suggestion->approved_title !== null
                        ? $suggestion->approved_title
                        : $suggestion->suggested_title;
                }
            }
        }

        if ($alt === null) {
            $alt = $this->readLocalizedValue($model, $map['alt'], $locale);
        }

        if ($title === null) {
            $title = $this->readLocalizedValue($model, $map['title'], $locale);
        }

        if ($alt === null) {
            $alt = $this->readLocalizedValue($model, 'alt', $locale);
        }

        if ($alt === null) {
            $alt = $this->readValue($model, $map['alt']);
        }

        if ($title === null) {
            $title = $this->readValue($model, $map['title']);
        }

        if (($alt === null || $title === null) && $identity !== null && $canUseSuggestionLookup) {
            $currentSuggestion = $this->currentSuggestion($model, $field, $locale, $resolvedImagePath);

            if ($currentSuggestion !== null) {
                if ($alt === null) {
                    $alt = $currentSuggestion->current_alt;
                }

                if ($title === null) {
                    $title = $currentSuggestion->current_title;
                }
            }
        }

        $alt = $alt === null ? $fallbackAlt : $alt;
        $title = $title === null ? $fallbackTitle : $title;

        return [
            'alt' => $this->plainTextOrNull($alt),
            'title' => $this->plainTextOrNull($title),
        ];
    }

    /**
     * @param mixed $model
     * @param string|null $field
     * @param string $locale
     * @return mixed
     */
    private function readLocalizedValue($model, ?string $field, string $locale)
    {
        if ($field === null || $field === '') {
            return null;
        }

        $localizedField = $field . '_' . $locale;
        $value = $this->readValue($model, $localizedField);

        return $value !== null ? $value : null;
    }

    /**
     * @param mixed $model
     * @return array<string, mixed>
     */
    private function targetForModel($model): array
    {
        $targets = (array) $this->config->get('alt_generation.targets', []);
        $identity = $this->identityFor($model);

        if ($identity !== null && isset($identity['class'], $targets[$identity['class']])
            && is_array($targets[$identity['class']])) {
            return $targets[$identity['class']];
        }

        if (!is_object($model)) {
            return [];
        }

        $modelClass = get_class($model);

        foreach ($targets as $class => $target) {
            if (is_string($class) && $model instanceof $class && is_array($target)) {
                return $target;
            }
        }

        return [];
    }

    /**
     * @param mixed $model
     * @param array<string, mixed> $target
     * @param string $field
     * @return array{alt: string|null, title: string|null}
     */
    private function applyMap($model, array $target, string $field): array
    {
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

        if (is_object($model) && method_exists($model, 'altFieldFor')) {
            $resolved = $model->altFieldFor($field);

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
     * Gallery and MediaLibrary entries must never resolve only by field.
     *
     * @param array<string, mixed> $target
     * @param string $field
     * @return bool
     */
    private function requiresExactImagePath(array $target, string $field): bool
    {
        if (strpos($field, 'media:') === 0) {
            return true;
        }

        $galleryFields = isset($target['gallery_fields']) && is_array($target['gallery_fields'])
            ? $target['gallery_fields']
            : [];

        return in_array($field, $galleryFields, true);
    }

    /**
     * @param mixed $model
     * @param string|null $field
     * @return mixed
     */
    private function readValue($model, ?string $field)
    {
        if ($field === null || $field === '') {
            return null;
        }

        if ($model instanceof Model) {
            if (array_key_exists($field, $model->getAttributes())) {
                return $model->getAttribute($field);
            }

            $value = $model->getAttribute($field);

            return $value !== null ? $value : null;
        }

        if (is_array($model)) {
            return array_key_exists($field, $model) ? $model[$field] : null;
        }

        if ($model instanceof ArrayAccess && isset($model[$field])) {
            return $model[$field];
        }

        if (is_object($model) && isset($model->{$field})) {
            return $model->{$field};
        }

        return null;
    }

    /**
     * @param mixed $model
     * @param string $field
     * @param string|null $imagePath
     * @return \App\Models\ImageAltSuggestion|null
     */
    private function appliedSuggestion(
        $model,
        string $field,
        string $locale,
        ?string $imagePath = null
    ): ?ImageAltSuggestion
    {
        return $this->suggestionByStatus($model, $field, $locale, $imagePath, ['applied', 'generated']);
    }

    /**
     * @param mixed $model
     * @param string $field
     * @param string $locale
     * @param string|null $imagePath
     * @return \App\Models\ImageAltSuggestion|null
     */
    private function currentSuggestion(
        $model,
        string $field,
        string $locale,
        ?string $imagePath = null
    ): ?ImageAltSuggestion
    {
        return $this->suggestionByStatus($model, $field, $locale, $imagePath, ['new', 'pending', 'approved', 'failed']);
    }

    /**
     * @param mixed $model
     * @param string $field
     * @param string $locale
     * @param string|null $imagePath
     * @param array<int, string> $statuses
     * @return \App\Models\ImageAltSuggestion|null
     */
    private function suggestionByStatus(
        $model,
        string $field,
        string $locale,
        ?string $imagePath,
        array $statuses
    ): ?ImageAltSuggestion
    {
        $identity = $this->identityFor($model);

        if ($identity === null) {
            return null;
        }

        $normalizedPath = $this->normalizeImagePath($imagePath);
        $cacheKey = implode(':', [
            implode(',', $identity['types']),
            $identity['id'],
            $field,
            $locale,
            $normalizedPath,
            implode(',', $statuses),
        ]);

        if (array_key_exists($cacheKey, $this->suggestionCache)) {
            return $this->suggestionCache[$cacheKey];
        }

        try {
            $query = ImageAltSuggestion::query()
                ->whereIn('imageable_type', $identity['types'])
                ->where('imageable_id', $identity['id'])
                ->where('field', $field)
                ->whereIn('status', $statuses)
                ->where('locale', $locale)
                ->orderByDesc('applied_at')
                ->orderByDesc('generated_at')
                ->orderByDesc('id');

            if ($normalizedPath !== null) {
                $query->where('image_path', $normalizedPath);
            }

            $this->suggestionCache[$cacheKey] = $query->first();

            if ($this->suggestionCache[$cacheKey] === null) {
                $fallbackQuery = ImageAltSuggestion::query()
                    ->whereIn('imageable_type', $identity['types'])
                    ->where('imageable_id', $identity['id'])
                    ->where('field', $field)
                    ->whereIn('status', $statuses)
                    ->whereNull('locale')
                    ->orderByDesc('applied_at')
                    ->orderByDesc('generated_at')
                    ->orderByDesc('id');

                if ($normalizedPath !== null) {
                    $fallbackQuery->where('image_path', $normalizedPath);
                }

                $this->suggestionCache[$cacheKey] = $fallbackQuery->first();
            }
        } catch (Throwable $exception) {
            $this->suggestionCache[$cacheKey] = null;
        }

        return $this->suggestionCache[$cacheKey];
    }

    /**
     * Build a safe lookup identity for Eloquent models and explicit array descriptors.
     *
     * @param mixed $model
     * @return array{class: string, id: mixed, types: array<int, string>}|null
     */
    private function identityFor($model): ?array
    {
        if ($model instanceof Model) {
            if ($model->getKey() === null) {
                return null;
            }

            $types = [get_class($model)];
            $morphClass = $model->getMorphClass();

            if (is_string($morphClass) && $morphClass !== '' && !in_array($morphClass, $types, true)) {
                $types[] = $morphClass;
            }

            return [
                'class' => get_class($model),
                'id' => $model->getKey(),
                'types' => $types,
            ];
        }

        $class = $this->readValue($model, 'type')
            ?: $this->readValue($model, 'class')
            ?: $this->readValue($model, 'model_type')
            ?: $this->readValue($model, 'imageable_type');
        $id = $this->readValue($model, 'id')
            ?: $this->readValue($model, 'model_id')
            ?: $this->readValue($model, 'imageable_id');

        if (!is_string($class) || $class === '' || $id === null || $id === '') {
            return null;
        }

        if (!class_exists($class) || !is_subclass_of($class, Model::class)) {
            return null;
        }

        return [
            'class' => $class,
            'id' => $id,
            'types' => [$class],
        ];
    }

    /**
     * Infer a single-image field path for legacy @altAttrs($model, 'field') calls.
     *
     * @param mixed $model
     * @param string $field
     * @return string|null
     */
    private function inferImagePath($model, string $field): ?string
    {
        if (strpos($field, 'media:') === 0) {
            return null;
        }

        $value = $this->readValue($model, $field);

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    private function plainTextOrNull($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_ireplace(['<br>', '<br/>', '<br />'], ' ', $text);
        $text = trim((string) preg_replace('/\s+/', ' ', strip_tags($text)));

        return $text === '' ? null : $text;
    }

    /**
     * @param string|null $path
     * @return string|null
     */
    private function normalizeImagePath(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $raw = trim($path);
        $decoded = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $first = reset($decoded);

            if (is_array($first)) {
                foreach (['download_link', 'path', 'url', 'src', 'image', 'img'] as $key) {
                    if (isset($first[$key]) && is_string($first[$key]) && trim($first[$key]) !== '') {
                        $raw = $first[$key];
                        break;
                    }
                }
            } elseif (is_string($first)) {
                $raw = $first;
            }
        }

        $parts = preg_split('/[?#]/', html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 2);
        $path = str_replace('\\', '/', $parts[0] ?? $raw);

        if (preg_match('#^https?://#i', $path)) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = ltrim($path, '/');

        if (strpos($path, 'public/storage/') === 0) {
            return substr($path, 15);
        }

        return strpos($path, 'storage/') === 0 ? substr($path, 8) : $path;
    }
}
