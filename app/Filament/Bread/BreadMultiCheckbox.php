<?php

namespace App\Filament\Bread;

use Illuminate\Validation\ValidationException;

class BreadMultiCheckbox
{
    public function validate(array $details): void
    {
        if (! isset($details['options']) || ! is_array($details['options']) || count($details['options']) > 500
            || array_filter($details['options'], fn ($label) => ! is_scalar($label)) !== [] || (isset($details['checked']) && ! is_scalar($details['checked']))) {
            throw ValidationException::withMessages(['details' => 'multiple_checkbox: укажите options с подписями (до 500) и scalar checked.']);
        }
    }

    public function state(mixed $value, array $details): ?array
    {
        if (! is_array($details['options'] ?? null)) { return null; }
        if ($value === null) { return ! empty($details['checked']) ? array_map('strval', array_keys($details['options'] ?? [])) : []; }
        if (is_string($value)) {
            try { $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR); } catch (\JsonException) { return null; }
        }
        if (! is_array($value)) { return null; }
        $keys = [];
        foreach (array_values($value) as $key) {
            if ((! is_string($key) && ! is_int($key)) || ! array_key_exists((string) $key, $details['options'] ?? [])) { return null; }
            $keys[] = (string) $key;
        }
        return array_values(array_unique($keys));
    }

    public function encode(mixed $state, array $details, string $path, mixed $original = null): string
    {
        $this->validate($details);
        if (! is_array($state) || ! array_is_list($state) || count($state) > 500) { throw ValidationException::withMessages([$path => 'Некорректные варианты группы чекбоксов.']); }
        $keys = [];
        foreach ($state as $key) {
            if ((! is_string($key) && ! is_int($key)) || ! array_key_exists((string) $key, $details['options']) || in_array((string) $key, $keys, true)) {
                throw ValidationException::withMessages([$path => 'Выберите только доступные варианты без повторений.']);
            }
            $keys[] = (string) $key;
        }
        if (is_string($original) && $this->state($original, $details) === $keys) { return $original; }
        return json_encode(array_combine($keys, $keys), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public function summary(mixed $value, array $details): string
    {
        if (is_string($value)) { $value = json_decode($value, true); }
        if (! is_array($value)) { return ''; }
        $options = is_array($details['options'] ?? null) ? array_filter($details['options'], 'is_scalar') : [];
        return implode(', ', array_map(fn ($key) => is_scalar($key) ? (string) ($options[$key] ?? $key) : '', array_values($value)));
    }
}
