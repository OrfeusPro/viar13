<?php

namespace App\Support;

use Illuminate\Http\Request;

class StorefrontLocale
{
    public static function fromRequest(Request $request): string
    {
        $supported = array_keys((array) config('laravellocalization.supportedLocales', []));
        $default = app('laravellocalization')->getDefaultLocale();

        $candidates = [
            $request->input('locale'),
            $request->route('locale'),
            self::firstPathSegment($request->getPathInfo()),
            self::firstPathSegment((string) parse_url((string) $request->headers->get('referer'), PHP_URL_PATH)),
            app()->getLocale(),
            $default,
        ];

        foreach ($candidates as $candidate) {
            $candidate = strtolower(trim((string) $candidate));

            if (in_array($candidate, $supported, true)) {
                return $candidate;
            }
        }

        return $default;
    }

    private static function firstPathSegment(string $path): string
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        return $segments[0] ?? '';
    }
}
