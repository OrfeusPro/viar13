<?php

namespace App\Filament\Bread;

use Illuminate\Validation\ValidationException;

class BreadJsonRows
{
    public function configuredFields(array $details): array
    {
        $fields = $details['json_fields'] ?? ['key' => 'Key', 'value' => 'Value'];
        if (! is_array($fields) || $fields === []) { throw ValidationException::withMessages(['details' => 'json_fields должен содержать ключи и подписи колонок.']); }
        foreach ($fields as $key => $label) {
            if (! is_string($key) || ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key) || ! is_string($label) || $label === '') {
                throw ValidationException::withMessages(['details' => 'json_fields: используйте имена колонок без точек и текстовые подписи.']);
            }
        }
        return $fields;
    }

    public function document(mixed $value): ?array
    {
        if ($value === null || $value === '') { return ['fields' => [], 'rows' => []]; }
        if (is_string($value)) {
            try { $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR); } catch (\JsonException) { return null; }
        }
        if (! is_array($value) || ! isset($value['fields'], $value['rows']) || ! is_array($value['fields']) || ! is_array($value['rows'])) { return null; }
        foreach ($value['fields'] as $key => $label) {
            if (! $this->safeKey($key) || ! is_string($label)) { return null; }
        }
        foreach ($value['rows'] as $row) {
            if (! is_array($row)) { return null; }
            foreach ($row as $key => $cell) { if (! $this->safeKey($key) || (! is_scalar($cell) && $cell !== null)) { return null; } }
        }
        return $value;
    }

    private function safeKey(mixed $key): bool
    {
        return is_string($key) && (bool) preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key);
    }

    public function fields(array $details, array $document): array
    {
        $fields = array_replace($this->configuredFields($details), $document['fields']);
        foreach ($document['rows'] as $row) {
            foreach ($row as $key => $value) { $fields[$key] ??= $key; }
        }
        return $fields;
    }

    public function encode(array $details, mixed $original, mixed $state, string $path): string
    {
        $document = $this->document($original);
        $fail = fn () => throw ValidationException::withMessages([$path => 'Некорректные строки JSON. Сохранение отменено.']);
        if ($document === null || ! is_array($state) || ! is_array($state['rows'] ?? null) || count($state['rows']) > 500) { $fail(); }
        $fields = $this->fields($details, $document);
        $rows = [];
        foreach ($state['rows'] as $row) {
            if (! is_array($row) || array_diff(array_keys($row), array_keys($fields)) !== []) { $fail(); }
            foreach ($row as $cell) { if ((! is_scalar($cell) && $cell !== null) || (is_string($cell) && strlen($cell) > 50000)) { $fail(); } }
            $rows[] = (object) $row;
        }
        $normalize = static function (array $rows) use ($fields): array {
            return array_map(static function (array $row) use ($fields): array {
                $row = array_replace(array_fill_keys(array_keys($fields), null), $row);
                ksort($row);
                return $row;
            }, array_values($rows));
        };
        if (is_string($original) && $original !== '' && $normalize($state['rows']) === $normalize($document['rows'])) {
            return $original;
        }
        $document['fields'] = (object) $fields;
        $document['rows'] = $rows;
        return json_encode($document, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public function summary(mixed $value): string
    {
        $document = $this->document($value);
        if ($document === null) { return is_scalar($value) ? (string) $value : ''; }
        $lines = [];
        foreach ($document['rows'] as $row) {
            $cells = [];
            foreach ($row as $key => $cell) { $cells[] = ($document['fields'][$key] ?? $key).': '.(string) $cell; }
            $lines[] = implode(' · ', $cells);
        }
        return implode('; ', $lines);
    }
}
