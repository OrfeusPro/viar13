<?php

namespace App\Support\Admin;

class OrderMediaUrl
{
    public static function resolve(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '' || preg_match('/[\x00-\x1f\x7f\\\\]/', $path)) {
            return null;
        }

        // Keep existing absolute links, but never render executable URL schemes.
        if (preg_match('~^https?://~i', $path)) {
            return filter_var($path, FILTER_VALIDATE_URL) ? $path : null;
        }
        if (str_starts_with($path, '//') || preg_match('/^[a-z][a-z0-9+.-]*:/i', $path)) {
            return null;
        }

        $relative = ltrim($path, '/');
        if ($relative === '' || preg_match('~(^|/)\.{1,2}(/|$)~', rawurldecode($relative))) {
            return null;
        }

        $base = rtrim((string) config('admin_migration.order_media_base_url'), '/');
        if (! preg_match('~^https?://~i', $base) || ! filter_var($base, FILTER_VALIDATE_URL)) {
            return null;
        }

        // No file_exists, HTTP probes, downloads or local cache: files stay on production.
        return $base.'/'.str_replace(' ', '%20', $relative);
    }
}
