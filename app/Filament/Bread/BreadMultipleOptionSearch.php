<?php

namespace App\Filament\Bread;

use Illuminate\Database\Query\Builder;

class BreadMultipleOptionSearch
{
    public function applyEmpty(Builder $query, string $expression, array $bindings, string $type, string $search, string $operator): void
    {
        $label = mb_strtolower(BreadCaption::none());
        $needle = mb_strtolower($search);
        if (! ($operator === '=' ? $label === $needle : str_contains($label, $needle))) { return; }
        $document = "CASE WHEN JSON_VALID(($expression)) THEN ($expression) ELSE 'null' END";
        $driver = $query->getConnection()->getDriverName();
        $documentBindings = [...$bindings, ...$bindings];
        if ($driver === 'sqlite') {
            $sql = "JSON_TYPE(({document})) = 'null' OR (JSON_TYPE(({document})) = 'array' AND JSON_ARRAY_LENGTH(({document})) = 0)";
            $sql .= $type === 'multiple_checkbox'
                ? " OR (JSON_TYPE(({document})) = 'object' AND NOT EXISTS (SELECT 1 FROM JSON_EACH(({document}))))"
                : " OR JSON_TYPE(({document})) = 'false' OR (JSON_TYPE(({document})) IN ('integer', 'real') AND JSON_EXTRACT(({document}), '$') = 0) OR (JSON_TYPE(({document})) = 'text' AND CAST(JSON_EXTRACT(({document}), '$') AS TEXT) IN ('', '0'))";
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            $types = $type === 'multiple_checkbox' ? "('ARRAY', 'OBJECT')" : "('ARRAY')";
            $sql = "JSON_TYPE(({document})) = 'NULL' OR (JSON_TYPE(({document})) IN $types AND JSON_LENGTH(({document})) = 0)";
            if ($type === 'select_multiple') {
                $sql .= " OR (JSON_TYPE(({document})) = 'BOOLEAN' AND JSON_UNQUOTE(({document})) = 'false') OR (JSON_TYPE(({document})) IN ('INTEGER', 'DOUBLE') AND CAST(JSON_UNQUOTE(({document})) AS DECIMAL(65, 30)) = 0) OR (JSON_TYPE(({document})) = 'STRING' AND BINARY JSON_UNQUOTE(({document})) IN ('', '0'))";
            }
        } else { return; }
        $query->orWhereRaw('('.str_replace('{document}', $document, $sql).')',
            array_merge(...array_fill(0, substr_count($sql, '{document}'), $documentBindings)));
    }

    /** Search selected option values, never object property names or JSON substrings. */
    public function apply(Builder $query, string $expression, array $bindings, array $options, string $search, string $operator, bool $objectKeys = false): void
    {
        $needle = mb_strtolower($search);
        $keys = array_keys(array_filter($options, static function ($label) use ($needle, $operator): bool {
            if (! is_scalar($label)) { return false; }
            // Voyager escapes multiple captions: tags and entity text remain visible literals.
            $label = mb_strtolower((string) $label);
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
