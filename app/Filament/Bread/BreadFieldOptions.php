<?php

namespace App\Filament\Bread;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Illuminate\Validation\ValidationException;

class BreadFieldOptions
{
    public const TYPES = ['text', 'number', 'text_area'];

    private function fail(): never
    {
        throw ValidationException::withMessages(['details' => 'Некорректные параметры поля или неподдерживаемые правила validation.']);
    }

    private function parseRules(mixed $rules): array
    {
        if (is_string($rules)) { $rules = $rules === '' ? [] : explode('|', $rules); }
        if (! is_array($rules) || ! array_is_list($rules)) { $this->fail(); }
        foreach ($rules as $rule) {
            if (! is_string($rule) || (! preg_match('/^(?:required|nullable|sometimes|string|numeric|integer|boolean|email|url|alpha|alpha_dash|alpha_num|min:[+-]?\d+(?:\.\d+)?|max:[+-]?\d+(?:\.\d+)?|size:\d+(?:\.\d+)?|digits:\d+|digits_between:\d+,\d+|between:[+-]?\d+(?:\.\d+)?,[+-]?\d+(?:\.\d+)?|in:[^|]+|not_in:[^|]+)$/', $rule) && ! app(BreadValidation::class)->accepts($rule))) { $this->fail(); }
        }
        return $rules;
    }

    public function rules(array $details, string $operation): array
    {
        $validation = $details['validation'] ?? [];
        if (! is_array($validation)) { $this->fail(); }
        foreach (['add', 'edit'] as $action) {
            if (isset($validation[$action]) && ! is_array($validation[$action])) { $this->fail(); }
        }
        return array_merge($this->parseRules($validation['rule'] ?? []), $this->parseRules($validation[$operation]['rule'] ?? []));
    }

    public function validate(string $type, array $details): void
    {
        if (! in_array($type, self::TYPES, true)) { return; }
        $this->rules($details, 'add'); $this->rules($details, 'edit');
        $messages = $details['validation']['messages'] ?? [];
        if (! is_array($messages)) { $this->fail(); }
        foreach ($messages as $key => $message) {
            if (! is_string($key) || ! preg_match('/^[a-z_]+$/', $key) || ! is_string($message) || strlen($message) > 5000) { $this->fail(); }
        }
        if (isset($details['placeholder']) && (! is_string($details['placeholder']) || strlen($details['placeholder']) > 5000)) { $this->fail(); }
        if (isset($details['display']) && ! is_array($details['display'])) { $this->fail(); }
        if ($type === 'text_area' && isset($details['display']['rows']) && (filter_var($details['display']['rows'], FILTER_VALIDATE_INT) === false || (int) $details['display']['rows'] < 1 || (int) $details['display']['rows'] > 100)) { $this->fail(); }
        if (isset($details['default']) && ! is_scalar($details['default'])) { $this->fail(); }
        if ($type !== 'number') { return; }
        foreach (['min', 'max', 'step'] as $name) {
            if (! isset($details[$name]) || ($name === 'step' && $details[$name] === 'any')) { continue; }
            $value = $details[$name];
            if ((! is_string($value) && ! is_int($value) && ! is_float($value)) || ! is_numeric($value) || ! is_finite((float) $value) || ($name === 'step' && (float) $value <= 0)) { $this->fail(); }
        }
        if (isset($details['min'], $details['max']) && (float) $details['min'] > (float) $details['max']) { $this->fail(); }
    }

    public function numberRules(array $details): array
    {
        $rules = ['numeric'];
        if (isset($details['min'])) { $rules[] = 'min:'.$details['min']; }
        if (isset($details['max'])) { $rules[] = 'max:'.$details['max']; }
        $rules[] = static function (string $attribute, mixed $value, \Closure $fail) use ($details): void {
            if (! is_numeric($value)) { return; }
            if (! is_finite((float) $value)) { $fail('Число должно быть конечным.'); return; }
            if (! isset($details['step']) || $details['step'] === 'any') { return; }
            $base = (float) ($details['min'] ?? 0);
            $ratio = ((float) $value - $base) / (float) $details['step'];
            if (! is_finite($ratio) || abs($ratio - round($ratio)) > 0.0000001) { $fail('Число не соответствует заданному шагу.'); }
        };
        return $rules;
    }

    public function apply(Field $field, string $type, array $details, string $operation, bool $optionalTranslation, ?\Illuminate\Database\Eloquent\Model $model = null, ?int $recordId = null, ?string $locale = null): void
    {
        if (! in_array($type, self::TYPES, true)) { return; }
        try {
            $this->validate($type, $details);
            $rules = $this->rules($details, $operation);
            if ($model !== null) { $rules = app(BreadValidation::class)->compile($rules, $model, $field->getName(), $recordId, $locale); }
        }
        catch (ValidationException) {
            $field->disabled()->dehydrated(false)->helperText('Параметры или правила validation ещё не поддерживаются. Значение сохраняется без изменений.');
            return;
        }
        if ($optionalTranslation) { $rules = array_values(array_filter($rules, fn ($rule) => $rule !== 'required')); }
        $field->rules($rules)->validationMessages($details['validation']['messages'] ?? []);
        if ($field instanceof TextInput || $field instanceof Textarea) {
            $field->placeholder($details['placeholder'] ?? ($type === 'text_area' ? null : $field->getLabel()));
        }
        if ($field instanceof Textarea) { $field->rows((int) ($details['display']['rows'] ?? 5)); }
        if ($type === 'number' && $field instanceof TextInput) {
            if (isset($details['min'])) { $field->minValue($details['min']); }
            if (isset($details['max'])) { $field->maxValue($details['max']); }
            $field->step($details['step'] ?? 'any')->rules(array_map(fn ($rule) => $rule instanceof \Closure ? fn () => $rule : $rule, $this->numberRules($details)));
        }
    }
}
