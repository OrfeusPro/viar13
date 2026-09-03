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

    /**
     * Build an internal storefront URL for the requested/current locale.
     *
     * The default Russian storefront intentionally has no locale prefix.
     * External and non-HTTP links are returned unchanged.
     */
    public static function url(string $url, ?string $locale = null): string
    {
        $url = trim($url);

        if ($url === '' || $url[0] === '#' || preg_match('#^(?:mailto:|tel:|javascript:|data:)#i', $url)) {
            return $url;
        }

        $supported = array_keys((array) config('laravellocalization.supportedLocales', []));
        $default = app('laravellocalization')->getDefaultLocale();
        $locale = strtolower(trim((string) ($locale ?: app()->getLocale())));

        if (!in_array($locale, $supported, true)) {
            $locale = $default;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        if (!empty($parts['host']) && !self::isInternalHost($parts['host'])) {
            return $url;
        }

        $path = '/' . ltrim((string) ($parts['path'] ?? '/'), '/');
        $segments = array_values(array_filter(explode('/', trim($path, '/')), 'strlen'));

        if (isset($segments[0]) && in_array(strtolower($segments[0]), $supported, true)) {
            array_shift($segments);
        }

        $hideDefault = (bool) config('laravellocalization.hideDefaultLocaleInURL', false);
        if (!$hideDefault || $locale !== $default) {
            array_unshift($segments, $locale);
        }

        $localizedPath = '/' . implode('/', $segments);
        if ($localizedPath !== '/' && substr($path, -1) === '/') {
            $localizedPath .= '/';
        }

        $localizedUrl = url($localizedPath);
        if (isset($parts['query']) && $parts['query'] !== '') {
            $localizedUrl .= '?' . $parts['query'];
        }
        if (isset($parts['fragment']) && $parts['fragment'] !== '') {
            $localizedUrl .= '#' . $parts['fragment'];
        }

        return $localizedUrl;
    }

    private static function isInternalHost(string $host): bool
    {
        $hosts = array_filter([
            parse_url((string) config('app.url'), PHP_URL_HOST),
            request()->getHost(),
            'viarcanvas.com',
            'www.viarcanvas.com',
        ]);

        return in_array(strtolower($host), array_map('strtolower', $hosts), true);
    }

    private static function firstPathSegment(string $path): string
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        return $segments[0] ?? '';
    }
}
