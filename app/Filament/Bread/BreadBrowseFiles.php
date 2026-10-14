<?php

namespace App\Filament\Bread;

use Illuminate\Support\Facades\Storage;

class BreadBrowseFiles
{
    public static function url(mixed $path): ?string
    {
        if (! is_string($path) || $path === '') { return null; }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return in_array(strtolower(parse_url($path, PHP_URL_SCHEME) ?: ''), ['http', 'https'], true) ? $path : null;
        }
        if (str_starts_with($path, '/') || str_contains($path, '\\') || str_contains($path, ':')
            || preg_match('/[\x00-\x1f\x7f]/', $path) || in_array('..', explode('/', $path), true)) { return null; }
        $encoded = implode('/', array_map(rawurlencode(...), explode('/', $path)));
        return Storage::disk(config('voyager.storage.disk', 'public'))->url($encoded);
    }

    public static function links(mixed $value): array
    {
        if (! is_string($value) && ! is_array($value)) { return []; }
        $decoded = is_array($value) ? $value : json_decode($value, true);
        if ($decoded === null && is_string($value)) {
            $url = self::url($value);
            return $url ? [['url' => $url, 'name' => 'Download']] : [];
        }
        if (! is_array($decoded)) { return []; }
        $links = [];
        foreach ($decoded as $file) {
            if (! is_array($file) || ! ($url = self::url($file['download_link'] ?? null))) { continue; }
            $links[] = ['url' => $url, 'name' => is_scalar($file['original_name'] ?? null) ? (string) $file['original_name'] : ''];
        }
        return $links;
    }

    public static function media(mixed $value, array $details): array
    {
        $decoded = is_array($value) ? $value : (is_string($value) ? json_decode($value, true) : null);
        $items = is_array($decoded) ? array_values($decoded) : (is_string($value) && $value !== '' ? [$value] : []);
        return ['items' => array_slice($items, 0, 3), 'more' => max(0, count($items) - 3),
            'empty' => $items === [], 'images' => ! empty($details['show_as_images'])];
    }
}
