<?php

namespace App\Support\Admin;

class SaChatAttachment
{
    public static function describe(mixed $attachment): array
    {
        if (! is_array($attachment)) {
            return ['url' => null, 'name' => 'Вложение недоступно', 'image' => false];
        }
        $path = $attachment['local_path'] ?? $attachment['path'] ?? null;
        $name = $attachment['name'] ?? $attachment['original_name'] ?? (is_string($path) ? basename($path) : null);
        $name = is_string($name) && trim($name) !== '' ? $name : 'Вложение';
        $url = null;
        if (($attachment['status'] ?? 'stored') !== 'rejected' && is_string($path) && trim($path) !== '') {
            $path = trim($path);
            if (! preg_match('~^[a-z][a-z0-9+.-]*:|^//~i', $path)) {
                $path = ltrim($path, '/');
                $path = str_starts_with($path, 'storage/') ? $path : 'storage/'.$path;
                $url = OrderMediaUrl::resolve($path);
                $local = substr($path, strlen('storage/'));
                if ($url && ($attachment['disk'] ?? null) === 'public' && \Illuminate\Support\Facades\Storage::disk('public')->exists($local)) {
                    $url = \Illuminate\Support\Facades\Storage::disk('public')->url(implode('/', array_map('rawurlencode', explode('/', $local))));
                }
            }
        }
        $extension = $url ? strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) : '';

        return ['url' => $url, 'name' => $name, 'image' => in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)];
    }
}
