<?php

namespace TCG\Voyager\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use TCG\Voyager\Models\Translation;
use TCG\Voyager\Translator;

trait Translatable
{
    public static function scopeWhereTranslation(
        Builder $query,
        string $field,
        string $operator,
        mixed $value = null,
        string|array|null $locales = null,
        bool $default = true
    ): Builder {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $model = new static();
        $locales = is_string($locales) ? [$locales] : $locales;
        $keys = Translation::query()
            ->where('table_name', $model->getTable())
            ->where('column_name', $field)
            ->where('value', $operator, $value)
            ->when($locales, fn (Builder $translationQuery) => $translationQuery->whereIn('locale', $locales))
            ->pluck('foreign_key');

        return $query->where(function (Builder $builder) use ($model, $keys, $default, $field, $operator, $value): void {
            $builder->whereIn($model->getKeyName(), $keys);
            if ($default) {
                $builder->orWhere($field, $operator, $value);
            }
        });
    }

    public function translatable(): bool
    {
        return $this->getTranslatableAttributes() !== [];
    }

    public function getTranslatableAttributes(): array
    {
        return property_exists($this, 'translatable') && is_array($this->translatable)
            ? $this->translatable
            : [];
    }

    public function scopeWithTranslation(Builder $query, ?string $locale = null, string|bool $fallback = true): Builder
    {
        return $this->scopeWithTranslations($query, $locale, $fallback);
    }

    public function scopeWithTranslations(Builder $query, string|array|null $locales = null, string|bool $fallback = true): Builder
    {
        $locales ??= app()->getLocale();
        $locales = is_array($locales) ? $locales : [$locales];
        if ($fallback === true) {
            $fallback = config('app.fallback_locale', 'en');
        }
        if (is_string($fallback)) {
            $locales[] = $fallback;
        }

        return $query->with(['translations' => function (Relation $relation) use ($locales): void {
            $relation->whereIn('locale', array_values(array_unique($locales)));
        }]);
    }

    public function getTranslatedAttribute(string $attribute, ?string $locale = null, string|bool $fallback = true): mixed
    {
        return $this->getTranslatedAttributeMeta($attribute, $locale, $fallback)[0];
    }

    public function getTranslatedAttributeMeta(string $attribute, ?string $locale = null, string|bool $fallback = true): array
    {
        $locale ??= app()->getLocale();
        $default = config('voyager.multilingual.default', 'en');

        if (! in_array($attribute, $this->getTranslatableAttributes(), true) || $locale === $default) {
            return [$this->getAttribute($attribute), $default, true];
        }

        if (! $this->relationLoaded('translations')) {
            $this->load('translations');
        }

        $translations = $this->getRelation('translations')->where('column_name', $attribute);
        $translation = $translations->firstWhere('locale', $locale);
        if ($translation) {
            return [$translation->value, $locale, true];
        }

        $fallback = $fallback === true ? config('app.fallback_locale', 'en') : $fallback;
        if ($fallback === $default) {
            return [$this->getAttribute($attribute), $locale, false];
        }

        if (is_string($fallback) && ($translation = $translations->firstWhere('locale', $fallback))) {
            return [$translation->value, $fallback, true];
        }

        return [$this->getAttribute($attribute), $locale, false];
    }

    public function translations()
    {
        return $this->hasMany(Translation::class, 'foreign_key', $this->getKeyName())
            ->where('table_name', $this->getTable());
    }

    public function translate(?string $locale = null, string|bool $fallback = true)
    {
        return (new Translator($this))->translate($locale, $fallback);
    }
}
