<?php

namespace App\Services\SeoMetaGeneration;

use App\Models\SeoMetaSuggestion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SeoMetaModerationService
{
    public function __construct(private SeoMetaGenerator $generator, private SeoMetaContextResolver $contextResolver) {}

    public function approve(SeoMetaSuggestion $suggestion, array $values, int $actorId): void
    {
        $values = Validator::make($values, [
            'meta_title' => ['nullable', 'string', 'max:' . config('seo_meta_generation.limits.meta_title_max', 60)],
            'meta_description' => ['nullable', 'string', 'max:' . config('seo_meta_generation.limits.meta_description_max', 155)],
            'seo_keywords' => ['nullable', 'string', 'max:1000'],
        ])->validate();
        if (! in_array($suggestion->status, ['generated', 'pending', 'approved'], true)) {
            throw ValidationException::withMessages(['meta_title' => 'Сначала сгенерируйте метаданные.']);
        }
        $title = trim((string) ($values['meta_title'] ?? $suggestion->suggested_meta_title));
        $description = trim((string) ($values['meta_description'] ?? $suggestion->suggested_meta_description));
        if ($title === '' && $description === '') {
            throw ValidationException::withMessages(['meta_title' => 'Нет метаданных для одобрения.']);
        }
        // Bulk approval must also validate generated values, not only form input.
        Validator::make(['meta_title' => $title, 'meta_description' => $description], [
            'meta_title' => ['max:' . config('seo_meta_generation.limits.meta_title_max', 60)],
            'meta_description' => ['max:' . config('seo_meta_generation.limits.meta_description_max', 155)],
        ])->validate();
        $suggestion->fill(['approved_meta_title' => $title, 'approved_meta_description' => $description,
            'reviewed_by' => $actorId, 'reviewed_at' => now(), 'error' => null, 'status' => 'approved']);
        if (array_key_exists('seo_keywords', $values)) {
            $suggestion->seo_keywords = trim((string) $values['seo_keywords']) ?: null;
        }
        $suggestion->save();
    }

    public function reject(SeoMetaSuggestion $suggestion, int $actorId): void
    {
        if (! in_array($suggestion->status, ['generated', 'pending', 'approved', 'failed'], true)) {
            throw ValidationException::withMessages(['status' => 'Отклонение недоступно в текущем статусе.']);
        }
        $suggestion->fill(['status' => 'rejected', 'reviewed_by' => $actorId, 'reviewed_at' => now()])->save();
    }

    public function generate(SeoMetaSuggestion $suggestion, bool $noCache = true): SeoMetaSuggestion
    {
        $entity = $this->entityFromSuggestion($suggestion);
        $target = $this->targetForSuggestion($suggestion);

        if (!$entity instanceof Model || $target === null) {
            $suggestion->error = 'Source entity or SEO target configuration is missing.';
            $suggestion->setStatus(SeoMetaSuggestion::STATUS_FAILED);
            $suggestion->save();

            return $suggestion;
        }

        try {
            $this->refreshSuggestionFromEntity($suggestion, $entity, $target);
            $result = $this->generator->generate($entity, [(string) $suggestion->locale], !$noCache, [
                'keywords' => $suggestion->seo_keywords,
            ]);
            $values = $result['locales'][(string) $suggestion->locale] ?? null;

            if (!is_array($values)) {
                throw new \RuntimeException('OpenAI response did not include locale: ' . (string) $suggestion->locale);
            }

            $suggestion->suggested_meta_title = (string) ($values['meta_title'] ?? '');
            $suggestion->suggested_meta_description = (string) ($values['meta_description'] ?? '');
            $suggestion->approved_meta_title = null;
            $suggestion->approved_meta_description = null;
            $suggestion->prompt_context = [
                'context' => $result['context'] ?? [],
                'context_json' => $result['context_json'] ?? null,
                'limits' => config('seo_meta_generation.limits', []),
            ];
            $suggestion->model = isset($result['model']) ? (string) $result['model'] : null;
            $suggestion->tokens_used = isset($result['tokens']) ? (int) $result['tokens'] : null;
            $suggestion->error = null;
            $suggestion->generated_at = Carbon::now();
            $suggestion->setStatus(SeoMetaSuggestion::STATUS_PENDING);
            $suggestion->save();
        } catch (\Throwable $exception) {
            $suggestion->error = $exception->getMessage();
            $suggestion->setStatus(SeoMetaSuggestion::STATUS_FAILED);
            $suggestion->save();
        }

        return $suggestion;
    }

    public function apply(SeoMetaSuggestion $suggestion, int $actorId): void
    {
        DB::transaction(function () use ($suggestion, $actorId): void {
        $suggestion = SeoMetaSuggestion::query()->lockForUpdate()->findOrFail($suggestion->id);
        if ($suggestion->status !== 'approved') {
            throw ValidationException::withMessages(['status' => 'Применение доступно только после одобрения.']);
        }
        $entity = $this->entityFromSuggestion($suggestion);
        $target = $this->targetForSuggestion($suggestion);

        if (!$entity instanceof Model || $target === null) {
            throw new \RuntimeException('Source entity or SEO target configuration is missing.');
        }

        $title = $suggestion->approved_meta_title;
        $description = $suggestion->approved_meta_description;

        if (trim((string) $title) === '' && trim((string) $description) === '') {
            throw new \RuntimeException('Generate or approve SEO meta first.');
        }

        DB::transaction(function () use ($entity, $target, $suggestion, $title, $description): void {
            if (trim((string) $title) !== '') {
                $this->writeTranslatedValue($entity, (string) $target['title_field'], (string) $title, (string) $suggestion->locale);
            }

            if (trim((string) $description) !== '') {
                $this->writeTranslatedValue($entity, (string) $target['description_field'], (string) $description, (string) $suggestion->locale);
            }
        });

        $suggestion->approved_meta_title = $title;
        $suggestion->approved_meta_description = $description;
        $suggestion->current_meta_title = $title;
        $suggestion->current_meta_description = $description;
        $suggestion->applied_by = $actorId;
        $suggestion->applied_at = Carbon::now();
        $suggestion->error = null;
        $suggestion->setStatus(SeoMetaSuggestion::STATUS_APPLIED);
        $suggestion->save();
        });
    }

    private function refreshSuggestionFromEntity(SeoMetaSuggestion $suggestion, Model $entity, array $target): void
    {
        $context = $this->contextResolver->resolve($entity, [(string) $suggestion->locale]);
        $localeContext = isset($context['locales'][$suggestion->locale]) && is_array($context['locales'][$suggestion->locale])
            ? $context['locales'][$suggestion->locale]
            : [];
        $entityContext = isset($context['entity']) && is_array($context['entity']) ? $context['entity'] : [];

        $suggestion->entity_label = (string) ($target['label'] ?? class_basename($entity));
        $suggestion->entity_title = isset($localeContext['source_title']) ? (string) $localeContext['source_title'] : (isset($localeContext['current_meta_title']) ? (string) $localeContext['current_meta_title'] : null);
        $suggestion->page_url = isset($localeContext['page_url']) ? (string) $localeContext['page_url'] : (isset($entityContext['url']) ? (string) $entityContext['url'] : null);
        $suggestion->title_field = (string) $target['title_field'];
        $suggestion->description_field = (string) $target['description_field'];
        $suggestion->current_meta_title = $this->readTranslatedValue($entity, (string) $target['title_field'], (string) $suggestion->locale);
        $suggestion->current_meta_description = $this->readTranslatedValue($entity, (string) $target['description_field'], (string) $suggestion->locale);
    }

    private function entityFromSuggestion(SeoMetaSuggestion $suggestion): ?Model
    {
        $class = (string) $suggestion->metaable_type;

        if ($this->targetForSuggestion($suggestion) === null || !class_exists($class) || !is_subclass_of($class, Model::class)
            || ! in_array($suggestion->locale, $this->contextResolver->supportedLocales(), true)) {
            return null;
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $class */
        return $class::query()->find((int) $suggestion->metaable_id);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function targetForSuggestion(SeoMetaSuggestion $suggestion): ?array
    {
        return $this->contextResolver->targetForModel((string) $suggestion->metaable_type);
    }

    private function readTranslatedValue(Model $entity, string $field, string $locale): ?string
    {
        if (method_exists($entity, 'getTranslatedAttribute')) {
            try {
                $value = $entity->getTranslatedAttribute($field, $locale, false);

                return $value === null ? null : (string) $value;
            } catch (\Throwable $exception) {
            }
        }

        if (!array_key_exists($field, $entity->getAttributes())) {
            return null;
        }

        $value = $entity->getAttribute($field);

        return $value === null ? null : (string) $value;
    }

    private function writeTranslatedValue(Model $entity, string $field, string $value, string $locale): void
    {
        if (method_exists($entity, 'getTranslatableAttributes')
            && in_array($field, $entity->getTranslatableAttributes(), true)
            && $locale !== config('voyager.multilingual.default', 'en')) {
            DB::table('translations')->updateOrInsert([
                'table_name' => $entity->getTable(), 'column_name' => $field,
                'foreign_key' => $entity->getKey(), 'locale' => $locale,
            ], ['value' => $value]);
            $entity->load('translations');

            return;
        }

        $entity->setAttribute($field, $value);
        $entity->save();
    }

}
