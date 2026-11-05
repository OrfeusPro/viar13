<?php

namespace App\Filament\Bread;

use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class BreadCodeEditor
{
    public function language(array $details): ?Language
    {
        $value = $details['language'] ?? 'text';
        if (! is_string($value)) { throw ValidationException::withMessages(['details' => 'language должен быть текстом.']); }
        $value = strtolower(trim($value));
        $value = ['js' => 'javascript', 'yml' => 'yaml', 'c++' => 'cpp', 'plain_text' => 'text', 'plaintext' => 'text'][$value] ?? $value;
        if ($value === '' || $value === 'text') { return null; }
        return Language::tryFrom($value) ?? throw ValidationException::withMessages(['details' => 'Этот язык не поддерживается редактором кода Filament.']);
    }

    public function validate(array $details): void
    {
        $this->language($details);
        if (isset($details['theme']) && (! is_string($details['theme']) || strlen($details['theme']) > 100)) {
            throw ValidationException::withMessages(['details' => 'theme должен быть строкой до 100 символов.']);
        }
        if (isset($details['default']) && ! is_string($details['default'])) {
            throw ValidationException::withMessages(['details' => 'default для code_editor должен быть текстом.']);
        }
    }

    public function component(string $field, array $details, Model $model): CodeEditor
    {
        try { $language = $this->language($details); $note = null; }
        catch (ValidationException) { $language = null; $note = 'Язык из старых настроек не поддерживается; используется текстовый режим.'; }
        if (! isset($details['language'])) {
            $language = match ($model->getTable().'.'.$field) {
                'glob_config.analytics', 'glob_config.analytics_body', 'blog_reklama_type.source' => Language::Html,
                'abandoned_carts.cart_data' => Language::Json,
                default => null,
            };
        }
        $cast = $model->getCasts()[$field] ?? null;
        if (in_array($cast, ['array', 'json'], true) && ! isset($details['language'])) { $language = Language::Json; }
        if (isset($details['theme']) && $details['theme'] !== '') {
            $note = trim(($note ?? '').' Цветовая тема следует светлому/тёмному режиму админки.');
        }
        $editor = CodeEditor::make($field)->language($language)->extraAttributes(['style' => 'min-height: 200px'])->rules(['nullable', 'string']);
        if ($note) { $editor->helperText($note); }
        if ($cast !== null && ! in_array($cast, ['string', 'array', 'json'], true)) {
            $editor->disabled()->dehydrated(false)->helperText('Cast модели несовместим с текстовым редактором. Значение сохраняется без изменений.');
        }
        return $editor;
    }

    public function state(Model $record, string $field, array $details): ?string
    {
        $value = $record->exists ? $record->getRawOriginal($field) : null;
        $value ??= $details['default'] ?? null;
        return is_scalar($value) ? (string) $value : null;
    }

    public function assign(Model $record, string $field, mixed $value, string $path): void
    {
        if ($value !== null && ! is_string($value)) {
            throw ValidationException::withMessages([$path => 'Ожидается текст кода.']);
        }
        if ($record->exists && $record->getRawOriginal($field) === $value) { return; }
        $cast = $record->getCasts()[$field] ?? null;
        if (in_array($cast, ['array', 'json'], true)) {
            if ($value !== null) {
                try { $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR); }
                catch (\JsonException) { throw ValidationException::withMessages([$path => 'Некорректный JSON для поля с JSON-cast модели.']); }
            }
        } elseif ($cast !== null && $cast !== 'string') {
            throw ValidationException::withMessages([$path => 'Cast модели несовместим с редактором кода.']);
        }
        $record->setAttribute($field, $value);
    }
}
