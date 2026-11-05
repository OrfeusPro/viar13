<?php

namespace App\Services\AltGeneration;

use App\Models\ImageAltApplyLog;
use App\Models\ImageAltSuggestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AltSuggestionReverter
{
    public function revert(ImageAltSuggestion $suggestion): void
    {
        DB::transaction(function () use ($suggestion): void {
            $suggestion = ImageAltSuggestion::query()->lockForUpdate()->findOrFail($suggestion->id);
            $logs = $suggestion->applyLogs()->orderByDesc('id')->get();
            if ($logs->isEmpty()) {
                if ($suggestion->status !== ImageAltSuggestion::STATUS_APPLIED) {
                    throw ValidationException::withMessages(['revert' => 'Нет журнала применения для отката.']);
                }
            } else {
                $entity = $this->resolveEntity($suggestion);
                if (! $entity) {
                    throw ValidationException::withMessages(['revert' => 'Не найден источник изображения.']);
                }
                foreach ($logs as $log) {
                    $field = (string) $log->field;
                    $column = (string) $log->column_written;
                    if ($this->isMediaApplyLog($log, $suggestion)) {
                        $this->writeMediaCustomProperty($entity, $suggestion, $log, $field, $column);
                    } else {
                        $locale = app(LocaleResolver::class)->normalizeOrDefault($log->locale ?: $suggestion->locale);
                        $preferTranslation = $field !== '' && $field === $column && $this->isConfiguredHtmlField($entity, $field);
                        if (! $this->writeEntityAttribute($entity, $column, $log->old_value, $locale, $preferTranslation)) {
                            throw ValidationException::withMessages(['revert' => 'Поле из журнала больше недоступно.']);
                        }
                    }
                }
            }
            $suggestion->setStatus(ImageAltSuggestion::STATUS_APPROVED);
            $suggestion->applied_at = null;
            $suggestion->error = null;
            $suggestion->save();
        });
    }

    private function isMediaApplyLog(ImageAltApplyLog $log, ImageAltSuggestion $suggestion): bool
    {
        if ((string) $log->source_type === 'media') {
            return true;
        }

        $context = (array) $suggestion->prompt_context;
        $image = isset($context['image']) && is_array($context['image']) ? $context['image'] : [];

        return (string) ($image['source_type'] ?? '') === 'media' || strpos((string) $log->field, 'media:') === 0;
    }

    private function writeMediaCustomProperty(
        Model $entity,
        ImageAltSuggestion $suggestion,
        ImageAltApplyLog $log,
        string $field,
        string $column
    ): void {
        $context = (array) $suggestion->prompt_context;
        $image = isset($context['image']) && is_array($context['image']) ? $context['image'] : [];
        $collection = isset($image['collection_name']) && is_string($image['collection_name'])
            ? $image['collection_name']
            : null;

        if ($collection === null && strpos($field, 'media:') === 0) {
            $collection = substr($field, 6);
        }

        $mediaId = $log->media_id !== null
            ? (int) $log->media_id
            : (isset($image['media_id']) ? (int) $image['media_id'] : null);
        $media = $this->resolveMediaItem($entity, $collection, $mediaId, (string) $suggestion->image_path);

        if (! is_object($media) || ! method_exists($media, 'setCustomProperty') || ! method_exists($media, 'save')) {
            throw ValidationException::withMessages(['revert' => 'Медиа из журнала не найдено или недоступно для записи.']);
        }

        if ($log->old_value === null && method_exists($media, 'forgetCustomProperty')) {
            $media->forgetCustomProperty($column);
        } else {
            $media->setCustomProperty($column, $log->old_value);
        }

        $media->save();
    }

    private function resolveEntity(ImageAltSuggestion $suggestion): ?Model
    {
        $class = (string) $suggestion->imageable_type;

        if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            return null;
        }

        /** @var class-string<Model> $class */
        $entity = $class::query()->find($suggestion->imageable_id);

        return $entity instanceof Model ? $entity : null;
    }

    private function writeEntityAttribute(
        Model $entity,
        string $field,
        ?string $value,
        string $locale,
        bool $preferTranslation
    ): bool {
        if ($preferTranslation && $this->writeTranslatedAttribute($entity, $field, $value, $locale)) {
            return true;
        }

        if (array_key_exists($field, $entity->getAttributes())) {
            $entity->setAttribute($field, $value);
            $entity->save();

            return true;
        }

        return $this->writeTranslatedAttribute($entity, $field, $value, $locale);
    }

    private function writeTranslatedAttribute(Model $entity, string $field, ?string $value, string $locale): bool
    {
        if (method_exists($entity, 'getTranslatableAttributes')
            && in_array($field, $entity->getTranslatableAttributes(), true)) {
            if ($locale === config('voyager.multilingual.default', 'en')) {
                $entity->setAttribute($field, $value);
                $entity->save();
            } else {
                DB::table('translations')->updateOrInsert([
                    'table_name' => $entity->getTable(), 'column_name' => $field,
                    'foreign_key' => $entity->getKey(), 'locale' => $locale,
                ], ['value' => $value]);
            }

            return true;
        }
        if (! method_exists($entity, 'translateOrNew')) {
            return false;
        }

        try {
            $translation = $entity->translateOrNew($locale);

            if (is_object($translation)) {
                $translation->{$field} = $value;

                if (method_exists($translation, 'save')) {
                    $translation->save();

                    return true;
                }
            }
        } catch (\Throwable $exception) {
            return false;
        }

        return false;
    }

    private function isConfiguredHtmlField(Model $entity, string $field): bool
    {
        $target = $this->targetForEntity($entity);
        $htmlFields = isset($target['html_fields']) && is_array($target['html_fields'])
            ? $target['html_fields']
            : [];

        return in_array($field, $htmlFields, true);
    }

    /**
     * @return array<string, mixed>
     */
    private function targetForEntity(Model $entity): array
    {
        $targets = (array) config('alt_generation.targets', []);
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
     * @return mixed|null
     */
    private function resolveMediaItem(Model $entity, ?string $collection, ?int $mediaId, string $imagePath)
    {
        if ($collection === null || $collection === '' || ! method_exists($entity, 'getMedia')) {
            return null;
        }

        try {
            $items = $entity->getMedia($collection);
        } catch (\Throwable $exception) {
            return null;
        }

        if (! is_iterable($items)) {
            return null;
        }

        $normalizedImagePath = $this->normalizeImagePath($imagePath);

        foreach ($items as $media) {
            if (! is_object($media)) {
                continue;
            }

            if ($mediaId !== null && isset($media->id) && (int) $media->id === $mediaId) {
                return $media;
            }

            if (! method_exists($media, 'getUrl')) {
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
