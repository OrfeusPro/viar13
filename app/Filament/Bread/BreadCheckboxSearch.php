<?php

namespace App\Filament\Bread;

use Illuminate\Database\Query\Builder;

class BreadCheckboxSearch
{
    /** Match escaped on/off text with PHP's scalar truthiness, rather than stored 0/1 alone. */
    public function apply(Builder $query, string $expression, array $bindings, array $details, string $search, string $operator): void
    {
        $needle = mb_strtolower($search);
        $matches = static function (string $caption) use ($needle, $operator): bool {
            $caption = mb_strtolower($caption);
            return $operator === '=' ? $caption === $needle : str_contains($caption, $needle);
        };
        $on = $matches($details['on']);
        $off = $matches($details['off']);
        if ($on && $off) { $query->orWhereRaw('1 = 1'); return; }
        if (! $on && ! $off) { $query->orWhereRaw('1 = 0'); return; }
        $cast = $query->getConnection()->getDriverName() === 'sqlite' ? "CAST(($expression) AS TEXT)" : "BINARY CAST(($expression) AS CHAR)";
        $truth = "CASE WHEN ($expression) IS NOT NULL AND $cast NOT IN ('', '0') THEN 1 ELSE 0 END";
        $query->orWhereRaw($truth.' = ?', [...$bindings, ...$bindings, $on ? 1 : 0]);
    }
}
