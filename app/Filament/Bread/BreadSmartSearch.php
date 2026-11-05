<?php

namespace App\Filament\Bread;

class BreadSmartSearch
{
    public function containsPattern(string $term): string
    {
        return '%'.strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
    }

    /** DataTables 1.x smart-search tokens: spaces separate words; double quotes retain phrases. */
    public function terms(string $search): array
    {
        preg_match_all('/"[^"]+"|[^ ]+/u', $search, $matches);
        return array_values(array_unique(array_map(static function (string $term): string {
            if (preg_match('/^"(.*)"$/us', $term, $phrase)) { $term = $phrase[1]; }
            $quote = strpos($term, '"');
            return $quote === false ? $term : substr_replace($term, '', $quote, 1);
        }, $matches[0])));
    }
}
