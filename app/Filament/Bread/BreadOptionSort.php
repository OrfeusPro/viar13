<?php

namespace App\Filament\Bread;

use Illuminate\Database\Query\Builder;
use stdClass;

class BreadOptionSort
{
    /** Sort the standard client-side captions, before multilingual DOM replacement. */
    public function apply(Builder $query, string $column, stdClass $row, string $direction): bool
    {
        $details = json_decode($row->details ?: '{}', true) ?: [];
        if (! is_array($details)) { return false; }
        if (isset($details['view']) || isset($details['relationship'])
            || array_key_exists('browse_inline_checkbox', $details) || array_key_exists('browse_inline_editor', $details)) {
            return false;
        }
        $checkbox = $row->type === 'checkbox';
        if ($checkbox) {
            if (! is_string($details['on'] ?? null) || ! is_string($details['off'] ?? null)) { return false; }
            $options = [0 => $details['off'], 1 => $details['on']];
        } elseif (in_array($row->type, ['select_dropdown', 'radio_btn'], true) && is_array($details['options'] ?? null)) {
            $options = $details['options'];
            if ($options === [] || array_filter($options, fn ($label) => ! is_scalar($label)) !== []) { return false; }
        } else {
            return false;
        }

        // Rank the finite caption catalog in PHP, avoiding database-specific Unicode LOWER().
        // Empty/unknown static values render as an empty cell in the original.
        $labels = [''];
        foreach ($options as $label) {
            $label = (string) $label;
            if (! $checkbox) { $label = html_entity_decode(strip_tags($label), ENT_QUOTES | ENT_HTML5, 'UTF-8'); }
            $labels[] = mb_strtolower(trim($label));
        }
        $numeric = count(array_filter($labels, fn ($label) => $label !== '' && ! is_numeric($label))) === 0;
        $ordered = array_values(array_unique($labels));
        usort($ordered, function ($left, $right) use ($numeric): int {
            if ($left === '') { return $right === '' ? 0 : -1; }
            if ($right === '') { return 1; }
            return $numeric ? ((float) $left <=> (float) $right) : strcmp($left, $right);
        });
        $rank = [];
        $position = 0;
        foreach ($ordered as $index => $label) {
            if ($index > 0 && ! ($numeric && $label !== '' && $ordered[$index - 1] !== ''
                && (float) $label === (float) $ordered[$index - 1])) { $position++; }
            $rank[$label] = $position;
        }
        $ranks = array_map(fn ($label) => $rank[$label], $labels);
        $wrapped = $query->getGrammar()->wrap($column);
        $bindings = [];
        if ($checkbox) {
            $cast = $query->getConnection()->getDriverName() === 'sqlite' ? 'TEXT' : 'CHAR';
            $sql = "CASE WHEN {$wrapped} IS NOT NULL AND CAST({$wrapped} AS {$cast}) NOT IN ('', '0') THEN ? ELSE ? END";
            $bindings = [$ranks[2], $ranks[1]];
        } else {
            $sql = 'CASE';
            foreach (array_keys($options) as $index => $key) {
                $sql .= " WHEN {$wrapped} = ? THEN ?";
                array_push($bindings, (string) $key, $ranks[$index + 1]);
            }
            $sql .= ' ELSE ? END';
            $bindings[] = $ranks[0];
        }
        $query->reorder()->orderByRaw($sql.' '.($direction === 'asc' ? 'asc' : 'desc'), $bindings);
        return true;
    }
}
