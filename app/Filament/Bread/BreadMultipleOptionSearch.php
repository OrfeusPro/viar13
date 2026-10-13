<?php

namespace App\Filament\Bread;

use Illuminate\Database\Query\Builder;

class BreadMultipleOptionSearch
{
    /** Search selected option values, never object property names or JSON substrings. */
    public function apply(Builder $query, string $expression, array $bindings, array $options, string $search, string $operator, bool $objectKeys = false): void
    {
        $needle = mb_strtolower($search);
        $keys = array_keys(array_filter($options, static function ($label) use ($needle, $operator): bool {
            if (! is_scalar($label)) { return false; }
            $label = mb_strtolower(strip_tags((string) $label));
            return $operator === '=' ? $label === $needle : str_contains($label, $needle);
        }));
        if ($keys === []) { return; }

        $document = "CASE WHEN JSON_VALID(($expression)) THEN ($expression) ELSE '[]' END";
        $documentBindings = [...$bindings, ...$bindings];
        $driver = $query->getConnection()->getDriverName();
        $query->orWhere(function (Builder $nested) use ($keys, $document, $documentBindings, $driver, $objectKeys): void {
            if ($driver === 'sqlite') {
                $placeholders = implode(', ', array_fill(0, count($keys), '?'));
                $types = $objectKeys ? "('object')" : "('array', 'object')";
                $value = $objectKeys ? 'CAST(viar_option_values.key AS TEXT)' : 'CAST(viar_option_values.value AS TEXT)';
                $scalar = $objectKeys ? '1=1' : "viar_option_values.type IN ('text', 'integer')";
                $nested->whereRaw("JSON_TYPE(($document)) IN $types AND EXISTS (SELECT 1 FROM JSON_EACH(($document)) AS viar_option_values WHERE $scalar AND $value IN ($placeholders))",
                    [...$documentBindings, ...$documentBindings, ...array_map('strval', $keys)]);
            } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
                // JSON_CONTAINS recursively matches nested arrays; browse captions only read root values.
                $values = "CASE JSON_TYPE(($document)) WHEN 'ARRAY' THEN ($document) WHEN 'OBJECT' THEN JSON_EXTRACT(($document), '$.*') ELSE JSON_ARRAY() END";
                $valueBindings = [...$documentBindings, ...$documentBindings, ...$documentBindings];
                if ($objectKeys) {
                    $values = "CASE JSON_TYPE(($document)) WHEN 'OBJECT' THEN JSON_KEYS(($document)) ELSE JSON_ARRAY() END";
                    $valueBindings = [...$documentBindings, ...$documentBindings];
                }
                $placeholders = implode(', ', array_fill(0, count($keys), '?'));
                $selected = "JSON_EXTRACT(($values), CONCAT('$[', viar_option_values.position - 1, ']'))";
                $nested->whereRaw("EXISTS (SELECT 1 FROM JSON_TABLE(($values), '$[*]' COLUMNS (position FOR ORDINALITY)) AS viar_option_values WHERE JSON_TYPE($selected) IN ('STRING', 'INTEGER') AND BINARY JSON_UNQUOTE($selected) IN ($placeholders))",
                    [...$valueBindings, ...$valueBindings, ...$valueBindings, ...array_map('strval', $keys)]);
            } else {
                $nested->whereRaw('1 = 0');
            }
        });
    }
}
