<?php

namespace App\Support\Admin;

use App\Models\OilHeader;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use TCG\Voyager\Facades\Voyager;

class OrderProductPreview
{
    public static function legacyContent(string $content): HtmlString
    {
        $config = (new HtmlSanitizerConfig)->allowSafeElements()->allowLinkSchemes(['http', 'https', 'mailto', 'tel']);

        return new HtmlString((new HtmlSanitizer($config))->sanitize($content));
    }

    public static function svgUrl(array $product): ?string
    {
        $relative = self::svgPath($product);

        return $relative === null ? null : asset(str_replace(' ', '%20', $relative));
    }

    private static function svgPath(array $product): ?string
    {
        // The original SVG branch applies to these types, not every basket image.
        $eligible = isset($product['is_construct']) || isset($product['is_canvas_inter'])
            || isset($product['is_modular_inter']) || isset($product['is_oil_portrait'])
            || (isset($product['is_gall_with_img']) && $product['is_gall_with_img'] == 1);
        if (! $eligible || ! is_string($product['activeImage'] ?? null)) {
            return null;
        }
        $path = parse_url($product['activeImage'], PHP_URL_PATH);
        if (! is_string($path) || preg_match('/[\\\\\x00-\x1f\x7f]/', $path)) {
            return null;
        }
        $path = rawurldecode($path);
        if (preg_match('~(^|/)\.{1,2}(/|$)~', $path) || str_contains($path, '\\') || str_contains($path, "\0")) {
            return null;
        }
        $path = str_replace('/storage/', '/uploads/', '/'.ltrim($path, '/'));
        $position = strpos($path, 'uploads/');
        if ($position === false || ! preg_match('/\.(jpeg|png|jpg|heic|heif)$/i', $path)) {
            return null;
        }
        $relative = preg_replace('/\.(jpeg|png|jpg|heic|heif)$/i', '.svg', substr($path, $position));
        if (! Storage::disk('uploads')->fileExists($relative)) {
            return null;
        }

        return $relative;
    }

    public static function svgDocument(array $product): ?string
    {
        $relative = self::svgPath($product);
        if ($relative === null) {
            return null;
        }
        try {
            $contents = Storage::disk('uploads')->get($relative);
        } catch (\Throwable) {
            return null;
        }
        if (! is_string($contents) || stripos($contents, '<!DOCTYPE') !== false) {
            return null;
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new \DOMDocument;
            $loaded = $document->loadXML($contents, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $svg = $loaded ? $document->documentElement : null;
        if (! $svg || $svg->localName !== 'svg') {
            return null;
        }
        if (! $svg->hasAttribute('viewBox')) {
            $width = $svg->getAttribute('width');
            $height = $svg->getAttribute('height');
            if (preg_match('/^([0-9.]+)(?:px)?$/', $width, $w) && preg_match('/^([0-9.]+)(?:px)?$/', $height, $h)
                && (float) $w[1] > 0 && (float) $h[1] > 0) {
                $svg->setAttribute('viewBox', '0 0 '.$w[1].' '.$h[1]);
            }
        }

        // A document context displays the image references used by Voyager's inline SVG.
        // The caller must use an iframe with an empty sandbox: never inject this markup into the admin DOM.
        return '<!doctype html><html><head><meta charset="utf-8"><style>html,body{margin:0;width:100%;height:100%;overflow:hidden}body{display:flex;align-items:center;justify-content:center}svg{display:block;width:100%;height:100%;max-width:100%;max-height:100%}</style></head><body>'
            .$document->saveXML($svg).'</body></html>';
    }

    public static function oilPromoUrl(): ?string
    {
        $images = json_decode((string) OilHeader::getOilPromoImage(), true);
        $first = is_array($images) ? ($images[0] ?? null) : null;
        if (! is_string($first) || OrderMediaUrl::resolve($first) === null) {
            return null;
        }

        return OrderMediaUrl::resolve(Voyager::image($first));
    }
}
