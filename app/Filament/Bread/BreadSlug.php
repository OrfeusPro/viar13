<?php

namespace App\Filament\Bread;

use Illuminate\Validation\ValidationException;

class BreadSlug
{
    public function configuration(string $target, string $type, array $details, iterable $rows): ?array
    {
        if (! array_key_exists('slugify', $details)) { return null; }
        $fail = fn () => throw ValidationException::withMessages(['details' => 'slugify: укажите другое доступное поле text в origin; forceUpdate должен быть boolean.']);
        $config = $details['slugify'];
        if ($type !== 'text' || ! is_array($config) || ! is_string($config['origin'] ?? null)
            || ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $config['origin']) || $config['origin'] === $target
            || in_array($config['origin'], ['password', 'remember_token'], true)
            || array_diff(array_keys($config), ['origin', 'forceUpdate']) !== []
            || (array_key_exists('forceUpdate', $config) && ! is_bool($config['forceUpdate']))) { $fail(); }
        $source = collect($rows)->first(fn ($row) => $row->field === $config['origin']);
        if (! $source || $source->type !== 'text') { $fail(); }
        // Original helper emits data-slug-forceupdate=true whenever this key is present.
        return ['origin' => $config['origin'], 'force' => isset($config['forceUpdate'])];
    }

    public function generate(string $text): string
    {
        // Character map from Voyager slugify.js, Copyright 2017 Bruno Torrinha (MIT).
        // Keep its mappings rather than Str::slug: e.g. щ -> sh and ё -> yo.
        static $map;
        $map ??= json_decode(file_get_contents(__DIR__.'/slug-character-map.json'), true, 512, JSON_THROW_ON_ERROR);
        $text = strtr(mb_strtolower($text, 'UTF-8'), $map);
        return trim(preg_replace('/[^a-z0-9]+/', '-', $text), '-');
    }
}
