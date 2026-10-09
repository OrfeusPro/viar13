<?php

namespace App\Filament\Bread;

use Illuminate\Validation\ValidationException;

class BreadFieldsGroup
{
    public function document(mixed $value): ?array
    {
        if (is_string($value)) {
            try { $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR); } catch (\JsonException) { return null; }
        }
        if (! is_array($value) || ! is_array($value['fields'] ?? null) || $value['fields'] === []) { return null; }
        foreach ($value['fields'] as $key => $field) {
            if (! is_string($key) || ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key) || ! is_array($field)
                || ! in_array($field['type'] ?? null, ['text', 'number', 'textarea'], true) || ! is_string($field['label'] ?? null)
                || (! is_scalar($field['value'] ?? null) && ($field['value'] ?? null) !== null)) { return null; }
        }
        return $value;
    }

    public function values(array $document): array
    {
        return ['values' => array_map(fn ($field) => $field['value'] ?? null, $document['fields'])];
    }

    public function encode(array $details, mixed $original, mixed $state, string $path): string
    {
        $document = $this->document($original === null || $original === '' ? $details : $original);
        if (! $document || ! is_array($state['values'] ?? null) || array_diff(array_keys($state['values']), array_keys($document['fields'])) !== []
            || array_diff(array_keys($document['fields']), array_keys($state['values'])) !== []) {
            throw ValidationException::withMessages([$path => 'Некорректная группа полей. Сохранение отменено.']);
        }
        $rules = [];
        foreach ($document['fields'] as $key => $field) {
            $rules[$key] = $field['type'] === 'number' ? ['nullable', 'numeric'] : ['nullable', 'string', 'max:50000'];
        }
        try { validator($state['values'], $rules)->validate(); }
        catch (ValidationException $exception) { throw ValidationException::withMessages([$path => implode(' ', $exception->validator->errors()->all())]); }
        $unchanged = true;
        foreach ($document['fields'] as $key => &$field) {
            $value = $state['values'][$key] ?? null;
            $previous = $field['value'] ?? null;
            $same = $value === $previous || ($field['type'] === 'number' && is_numeric($value) && is_numeric($previous) && (string) $value === (string) $previous);
            if (! $same) { $unchanged = false; }
            $field['value'] = $value;
        }
        unset($field);
        if ($unchanged && is_string($original) && $original !== '') { return $original; }
        $document['fields'] = (object) $document['fields'];
        return json_encode($document, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public function summary(mixed $value): string
    {
        $document = $this->document($value);
        return $document ? implode(' · ', array_map(fn ($field) => $field['label'].': '.($field['value'] ?? ''), $document['fields'])) : (is_scalar($value) ? (string) $value : '');
    }
}
