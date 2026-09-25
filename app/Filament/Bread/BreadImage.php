<?php

namespace App\Filament\Bread;

use Illuminate\Support\Facades\Storage;

class BreadImage
{
    public static function urls(mixed $value): array
    {
        if (is_string($value) && str_starts_with(trim($value), '[')) {
            $value = json_decode($value, true) ?: [];
        }

        $paths = is_array($value) ? $value : [$value];
        $urls = [];
        foreach ($paths as $path) {
            if (! is_string($path)) {
                continue;
            }
            $path = trim($path);
            if ($path === '' || str_starts_with($path, '/') || str_contains($path, '..')
                || str_contains($path, '://') || str_contains($path, '\\')
                || ! preg_match('~\.(?:jpe?g|png|webp|gif|avif|svg)$~i', $path)) {
                continue;
            }
            $urls[] = Storage::disk('public')->url($path);
        }

        return array_values(array_unique($urls));
    }
}
