<?php

namespace TCG\Voyager;

use Illuminate\Database\Eloquent\Model;

class Translator
{
    public function __construct(private readonly Model $model)
    {
    }

    public function translate(?string $locale = null, string|bool $fallback = true): Model
    {
        $translated = clone $this->model;

        foreach ($this->model->getTranslatableAttributes() as $attribute) {
            $translated->setAttribute(
                $attribute,
                $this->model->getTranslatedAttribute($attribute, $locale, $fallback)
            );
        }

        return $translated;
    }
}
