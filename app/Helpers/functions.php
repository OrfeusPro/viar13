<?php
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;

if (!function_exists('str_trans')) {
    /**
     * Read a locale-specific fragment from legacy Voyager Extension strings.
     *
     * Supported formats:
     *   {{en}}English{{ru}}Русский
     *   [[en]]English[[ru]]Русский
     */
    function str_trans(?string $string, ?string $locale = null): string
    {
        if ($string === null || $string === '') {
            return '';
        }

        $locale = $locale ?: app()->getLocale();
        $openBracket = str_contains($string, '[[') ? '[[' : '{{';
        $closeBracket = $openBracket === '[[' ? ']]' : '}}';
        $fragments = explode($openBracket, $string);

        foreach ($fragments as $fragment) {
            if (str_starts_with($fragment, $locale . $closeBracket)) {
                return substr($fragment, strlen($locale . $closeBracket));
            }
        }

        return $fragments[0] ?? $string;
    }
}

if (!function_exists('ver_asset')) {
    function ver_asset($path, $secure = null): string
    {
        $timestamp = @filemtime(public_path($path)) ?: 0;
		if(!$timestamp)
		{
			$timestamp = date("Ymd");
		}

        return image_normalize_url_scheme(asset($path, $secure)) . '?' . $timestamp;
    }
}

if (!function_exists('single_image_url')) {
    function single_image_url(string $slug, $version = null): string
    {
        $slug = trim($slug, "/ \t\n\r\0\x0B");
        $url = url('/images_single/' . $slug . '.png');

        if ($version === null || $version === '') {
            return $url;
        }

        return $url . '?v=' . rawurlencode((string) $version);
    }
}

if (!function_exists('seo_image_version')) {
    function seo_image_version($date = null): int
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->getTimestamp();
        }

        if (is_numeric($date)) {
            return (int) $date;
        }

        if (is_string($date) && trim($date) !== '') {
            $timestamp = strtotime($date);
            if ($timestamp !== false) {
                return $timestamp;
            }
        }

        return time();
    }
}

if (!function_exists('seo_product_schema')) {
    function seo_product_schema(string $name, string $description, string $url, string $image, $price = null): array
    {
        $name = trim(strip_tags($name));
        $description = trim(strip_tags($description));

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $name,
            'description' => $description !== '' ? $description : $name,
            'image' => [$image],
            'brand' => [
                '@type' => 'Brand',
                'name' => trim(strip_tags((string) config('app.name', 'ViarCanvas'))) ?: 'ViarCanvas',
            ],
        ];

        if (is_numeric($price) && (float) $price > 0) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'url' => $url,
                'priceCurrency' => 'EUR',
                'price' => number_format((float) $price, 2, '.', ''),
                'availability' => 'https://schema.org/InStock',
                'itemCondition' => 'https://schema.org/NewCondition',
            ];
        }

        return $schema;
    }
}

if (!function_exists('sale_icon')) {
    function sale_icon($text): string
    {
        $orig = $text;
        $text = substr($text, -1);
        if(strpos($text,'h') !== false || strpos($text,'H') !== false) {
            $text = __('homepage_new.icon.hit');
            $text = '<div class="price_label border-icon_hit icon_hit">'.$text.'</div>';
        } elseif(strpos($text,'s') !== false|| strpos($text,'S') !== false) {
            $text = __('homepage_new.icon.super_deal');
            $text = '<div class="price_label border-icon_super_deal icon_super_deal">'.$text.'</div>';
        } elseif(strpos($text,'t') !== false|| strpos($text,'T') !== false) {
            $text = __('homepage_new.icon.top');
            $text = '<div class="price_label border-icon_top icon_top">'.$text.'</div>';
        } elseif(strpos($orig,'-') !== false) {
            $text = __('homepage_new.icon.sale');
            $text = '<div class="price_label border-icon_sale icon_sale">'.$text.'</div>';
            // $text = '';
        } else {
            $text = "";
        }

        return $text;
    }
}

if (!function_exists('hasSpecialLabel')) {
    /**
     * Check if size name has special label (HIT, TOP, SUPER DEAL)
     * @param string|null $sizeName
     * @return bool
     */
    function hasSpecialLabel($sizeName): bool
    {
        if (empty($sizeName)) {
            return false;
        }

        $lastChar = strtolower(substr($sizeName, -1));
        return in_array($lastChar, ['h', 's', 't']);
    }
}

if (!function_exists('getLabelType')) {
    /**
     * Get label type from size name
     * @param string|null $sizeName
     * @return string|null Returns 'hit', 'super_deal', 'top' or null
     */
    function getLabelType($sizeName): ?string
    {
        if (empty($sizeName)) {
            return null;
        }

        $lastChar = strtolower(substr($sizeName, -1));

        switch($lastChar) {
            case 'h':
                return 'hit';
            case 's':
                return 'super_deal';
            case 't':
                return 'top';
            default:
                return null;
        }
    }
}

if (!function_exists('image_webp_supported_extensions')) {
    /**
     * @return array<int, string>
     */
    function image_webp_supported_extensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
    }
}

if (!function_exists('image_is_external_url')) {
    function image_is_external_url(?string $image): bool
    {
        $image = trim((string) $image);

        return $image !== '' && (preg_match('#^https?://#i', $image) || strpos($image, '//') === 0);
    }
}

if (!function_exists('image_same_domain_hosts')) {
    /**
     * Domains that represent this project and may safely be normalized to HTTPS.
     *
     * @return array<int, string>
     */
    function image_same_domain_hosts(): array
    {
        $hosts = [
            parse_url((string) config('app.url'), PHP_URL_HOST),
            parse_url((string) config('app.asset_url'), PHP_URL_HOST),
            'viarcanvas.com',
            'www.viarcanvas.com',
            'gorakweb.online',
            'www.gorakweb.online',
        ];

        try {
            if (app()->bound('request')) {
                $hosts[] = request()->getHost();
            }
        } catch (\Throwable $exception) {
            // Request is not always available in CLI contexts.
        }

        return array_values(array_unique(array_filter(array_map('strtolower', $hosts))));
    }
}

if (!function_exists('image_is_same_domain_url')) {
    function image_is_same_domain_url(?string $url): bool
    {
        $url = trim((string) $url);
        if ($url === '') {
            return false;
        }

        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return false;
        }

        return in_array(strtolower($host), image_same_domain_hosts(), true);
    }
}

if (!function_exists('image_normalize_url_scheme')) {
    function image_normalize_url_scheme(?string $url): string
    {
        $url = (string) $url;
        $trimmed = trim($url);
        if ($trimmed === '') {
            return $url;
        }

        $host = parse_url(strpos($trimmed, '//') === 0 ? 'http:' . $trimmed : $trimmed, PHP_URL_HOST);
        $isLocalHost = in_array(strtolower((string) $host), ['localhost', '127.0.0.1', '::1'], true);

        if (strpos($trimmed, '//') === 0 && image_is_same_domain_url($trimmed) && !$isLocalHost) {
            return 'https:' . $trimmed;
        }

        if (stripos($trimmed, 'http://') === 0 && image_is_same_domain_url($trimmed) && !$isLocalHost) {
            return 'https://' . substr($trimmed, 7);
        }

        return $url;
    }
}

if (!function_exists('image_normalize_srcset')) {
    function image_normalize_srcset(string $srcset): string
    {
        $items = explode(',', $srcset);
        foreach ($items as &$item) {
            $item = preg_replace_callback(
                '/^(\s*)(\S+)(.*)$/',
                function (array $matches): string {
                    return $matches[1] . image_normalize_url_scheme($matches[2]) . $matches[3];
                },
                $item
            );
        }
        unset($item);

        return implode(',', $items);
    }
}

if (!function_exists('image_normalize_img_tag_urls')) {
    function image_normalize_img_tag_urls(string $imgTag): string
    {
        return preg_replace_callback(
            '/\b(src|srcset|data-src|data-lazy)\s*=\s*(["\'])(.*?)\2/i',
            function (array $matches): string {
                $value = $matches[1] === 'srcset'
                    ? image_normalize_srcset($matches[3])
                    : image_normalize_url_scheme($matches[3]);

                return $matches[1] . '=' . $matches[2] . $value . $matches[2];
            },
            $imgTag
        ) ?: $imgTag;
    }
}

if (!function_exists('image_asset_url')) {
    function image_asset_url(string $relativePath): string
    {
        $url = asset(ltrim(str_replace('\\', '/', $relativePath), '/'));

        return image_normalize_url_scheme(str_replace('viarcanvas.loc', 'viarcanvas.com', $url));
    }
}

if (!function_exists('image_public_file_info')) {
    /**
     * Resolve a public image reference to a local file path and public URL.
     *
     * @param string|null $image
     * @return array{path: string, relative: string, url: string}|null
     */
    function image_public_file_info(?string $image): ?array
    {
        $raw = trim((string) $image);
        if ($raw === '') {
            return null;
        }

        $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (image_is_external_url($raw)) {
            $host = parse_url($raw, PHP_URL_HOST);
            $allowedHosts = image_same_domain_hosts();

            if (!$host || !in_array(strtolower($host), $allowedHosts, true)) {
                return null;
            }

            $raw = parse_url($raw, PHP_URL_PATH) ?: '';
        }

        $pathOnly = preg_split('/[?#]/', $raw, 2)[0] ?? $raw;
        $pathOnly = str_replace('\\', '/', $pathOnly);

        $publicPath = str_replace('\\', '/', public_path());
        $absoluteCandidate = str_replace('\\', '/', $pathOnly);

        if (strpos($absoluteCandidate, $publicPath . '/') === 0) {
            $relative = ltrim(substr($absoluteCandidate, strlen($publicPath)), '/');

            return [
                'path' => public_path($relative),
                'relative' => $relative,
                'url' => image_asset_url($relative),
            ];
        }

        $relative = ltrim($pathOnly, '/');
        if (strpos($relative, 'public/') === 0) {
            $relative = substr($relative, 7);
        }

        $relative = preg_replace('#/{2,}#', '/', $relative);
        $localPath = public_path($relative);

        if (!file_exists($localPath) && strpos($relative, 'storage/') !== 0) {
            $storageRelative = 'storage/' . $relative;
            if (file_exists(public_path($storageRelative))) {
                $relative = $storageRelative;
                $localPath = public_path($relative);
            }
        }

        return [
            'path' => $localPath,
            'relative' => $relative,
            'url' => image_asset_url($relative),
        ];
    }
}

if (!function_exists('image_original_url')) {
    function image_original_url($image): string
    {
        $raw = trim((string) $image);
        if ($raw === '') {
            return '';
        }

        if (image_is_external_url($raw)) {
            if (image_is_same_domain_url($raw)) {
                $info = image_public_file_info($raw);

                return $info ? $info['url'] : image_normalize_url_scheme($raw);
            }

            return $raw;
        }

        $info = image_public_file_info($raw);

        return $info ? $info['url'] : image_asset_url($raw);
    }
}

if (!function_exists('image_webp_path')) {
    function image_webp_path(string $localPath): string
    {
        return (string) preg_replace('/\.[^.\/\\\\]+$/', '.webp', $localPath);
    }
}

if (!function_exists('image_webp_url')) {
    function image_webp_url($image, bool $generate = false): ?string
    {
        $info = image_public_file_info((string) $image);
        if ($info === null) {
            return null;
        }

        $extension = strtolower(pathinfo($info['path'], PATHINFO_EXTENSION));
        if ($extension === 'webp') {
            return $info['url'];
        }

        if (!in_array($extension, image_webp_supported_extensions(), true)) {
            return null;
        }

        if (!file_exists($info['path']) || @filesize($info['path']) <= 0) {
            return null;
        }

        $webpPath = image_webp_path($info['path']);
        $webpRelative = image_webp_path($info['relative']);

        if (file_exists($webpPath) && @filesize($webpPath) > 0) {
            return image_asset_url($webpRelative);
        }

        if (!$generate) {
            return null;
        }

        try {
            $directory = dirname($webpPath);
            if (!is_dir($directory)) {
                @mkdir($directory, 0755, true);
            }

            $webp = Image::make($info['path'])->encode('webp', 85);
            $webp->save($webpPath);
        } catch (\Throwable $exception) {
            return null;
        }

        if (!file_exists($webpPath) || @filesize($webpPath) <= 0) {
            return null;
        }

        return image_asset_url($webpRelative);
    }
}

if (!function_exists('image_picture_sources')) {
    /**
     * @return array{src: string, type: string|null, src_webp: string|null, type_webp: string|null}
     */
    function image_picture_sources($image, bool $generateWebp = false): array
    {
        $src = image_original_url($image);
        $type = function_exists('site_image_mime') ? site_image_mime($src) : null;
        $srcWebp = $type === 'image/webp' ? null : image_webp_url($image, $generateWebp);

        return [
            'src' => $src,
            'type' => $type,
            'src_webp' => $srcWebp,
            'type_webp' => $srcWebp ? 'image/webp' : null,
        ];
    }
}

if (!function_exists('format_webp')) {
    function format_webp($image): string
    {
        return image_webp_url($image, true) ?: image_original_url($image);
    }
}

if (!function_exists('render_content_image_is_inside_picture')) {
    function render_content_image_is_inside_picture(string $html, int $offset): bool
    {
        $before = substr($html, 0, $offset);
        $lastPictureOpen = strripos($before, '<picture');
        $lastPictureClose = strripos($before, '</picture>');

        return $lastPictureOpen !== false && ($lastPictureClose === false || $lastPictureOpen > $lastPictureClose);
    }
}

if (!function_exists('render_content_image_src_from_tag')) {
    function render_content_image_src_from_tag(string $imgTag): ?string
    {
        if (stripos($imgTag, '<img') === false) {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument('1.0', 'UTF-8');

        try {
            $html = '<?xml encoding="UTF-8"><div>' . $imgTag . '</div>';
            if (!$document->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
                return null;
            }

            $image = $document->getElementsByTagName('img')->item(0);
            if (!$image) {
                return null;
            }

            $src = trim((string) $image->getAttribute('src'));

            return $src !== '' ? $src : null;
        } catch (\Throwable $exception) {
            return null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}

if (!function_exists('render_content_images')) {
    function render_content_images($html): string
    {
        $html = (string) $html;
        if ($html === '' || stripos($html, '<img') === false) {
            return $html;
        }

        try {
            $result = preg_replace_callback(
                '/<img\b[^>]*>/i',
                function (array $matches) use ($html): string {
                    $imgTag = $matches[0][0];
                    $offset = $matches[0][1];

                    if (render_content_image_is_inside_picture($html, $offset)) {
                        return $imgTag;
                    }

                    $src = render_content_image_src_from_tag($imgTag);
                    if (!$src) {
                        return $imgTag;
                    }

                    $imgTag = image_normalize_img_tag_urls($imgTag);
                    $src = render_content_image_src_from_tag($imgTag);

                    if (!$src || stripos($src, 'data:image/') === 0) {
                        return $imgTag;
                    }

                    if (preg_match('#^(?:https?:)?//#i', $src) && !image_is_same_domain_url($src)) {
                        return $imgTag;
                    }

                    $path = parse_url($src, PHP_URL_PATH) ?: '';
                    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    if (
                        $extension === 'webp'
                        || $extension === 'svg'
                        || !in_array($extension, image_webp_supported_extensions(), true)
                    ) {
                        return $imgTag;
                    }

                    $webpUrl = image_webp_url($src, true);
                    if (!$webpUrl) {
                        return $imgTag;
                    }

                    $escapedWebpUrl = htmlspecialchars($webpUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                    return '<picture><source srcset="' . $escapedWebpUrl . '" type="image/webp">' . $imgTag . '</picture>';
                },
                $html,
                -1,
                $count,
                PREG_OFFSET_CAPTURE
            );

            return is_string($result) ? $result : $html;
        } catch (\Throwable $exception) {
            return $html;
        }
    }
}

if (!function_exists('site_image_mime')) {
    /**
     * Detect mime type for an url/relative path by extension.
     */
    function site_image_mime(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $ext  = strtolower(pathinfo((string)$path, PATHINFO_EXTENSION));

        switch ($ext) {
            case 'svg':
            case 'svgz':
                return 'image/svg+xml';
            case 'webp':
                return 'image/webp';
            case 'avif':
                return 'image/avif';
            case 'png':
                return 'image/png';
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'gif':
                return 'image/gif';
            case 'bmp':
                return 'image/bmp';
            default:
                return null;
        }
    }
}

if (!function_exists('site_image_alt_values')) {
    /**
     * Resolve SiteImage alt/title through applied suggestions first, then model columns.
     *
     * @param \App\Models\SiteImage|null $imageItem
     * @param string $field
     * @param string|null $imagePath
     * @param array<string, string> $fallback
     * @return array{alt: string, title: string}
     */
    function site_image_alt_values(
        ?\App\Models\SiteImage $imageItem,
        string $field,
        ?string $imagePath = null,
        array $fallback = []
    ): array {
        $plain = static function ($value): string {
            $text = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = str_ireplace(['<br>', '<br/>', '<br />'], ' ', $text);

            return trim((string) preg_replace('/\s+/', ' ', strip_tags($text)));
        };

        $alt = $plain($fallback['alt'] ?? '');
        $title = $plain($fallback['title'] ?? '');

        if (!$imageItem) {
            return [
                'alt' => $alt,
                'title' => $title,
            ];
        }

        try {
            $values = app(\App\Services\AltGeneration\AltAttributeResolver::class)
                ->valuesFor($imageItem, $field, $imagePath);

            if (isset($values['alt']) && trim((string) $values['alt']) !== '') {
                $alt = (string) $values['alt'];
            }

            if (isset($values['title']) && trim((string) $values['title']) !== '') {
                $title = (string) $values['title'];
            }
        } catch (\Throwable $exception) {
            // Keep legacy column/fallback values if the alt-generation services are unavailable.
        }

        return [
            'alt' => $plain($alt),
            'title' => $plain($title),
        ];
    }
}

if (!function_exists('site_image_build_default')) {
    /**
     * Build a default source set from a relative asset path.
     *
     * @param string $rel Path relative to public (e.g. env('THEME').'images/pic.jpg')
     * @param array $options ['check_webp' => bool, 'mime' => callable]
     */
    function site_image_build_default(string $rel, array $options = []): array
    {
        $mime      = $options['mime'] ?? 'site_image_mime';
        $checkWebp = $options['check_webp'] ?? true;

        $src      = ver_asset($rel);
        $type     = is_callable($mime) ? $mime($src) : null;
        $srcWebp  = null;
        $typeWebp = null;

        if ($checkWebp && function_exists('image_webp_url')) {
            $srcWebp = image_webp_url($rel);
            $typeWebp = $srcWebp ? 'image/webp' : null;
        }

        return [
            'src' => $src,
            'type' => $type,
            'src_webp' => $srcWebp,
            'type_webp' => $typeWebp,
            'alt' => (string)($options['alt'] ?? ''),
            'title' => (string)($options['title'] ?? ''),
        ];
    }
}

if (!function_exists('site_image_from_item')) {
    /**
     * Normalize SiteImage model to a single picture source set.
     *
     * @param \App\Models\SiteImage|null $imageItem
     * @param string|null $fallbackRel   Relative fallback asset path
     * @param array $options ['fallback_on_empty' => bool, 'mime' => callable]
     */
    function site_image_from_item(?\App\Models\SiteImage $imageItem, ?string $fallbackRel = null, array $options = []): array
    {
        $mime             = $options['mime'] ?? 'site_image_mime';
        $fallbackOnEmpty  = $options['fallback_on_empty'] ?? true;
        $fallback         = $fallbackRel ? site_image_build_default($fallbackRel, ['mime' => $mime] + $options) : null;

        if (!$imageItem) {
            return $fallback ?? [
                'src' => '',
                'type' => null,
                'src_webp' => null,
                'type_webp' => null,
                'alt' => '',
                'title' => '',
            ];
        }

        $version = ($imageItem->updated_at ? $imageItem->updated_at->timestamp : time());

        if (!empty($imageItem->svg_url)) {
            $fallbackAlt = trim((string)($imageItem->svg_alt ?? '')) !== ''
                ? (string) $imageItem->svg_alt
                : (string)($options['alt'] ?? '');
            $fallbackTitle = trim((string)($imageItem->svg_title ?? '')) !== ''
                ? (string) $imageItem->svg_title
                : (string)($options['title'] ?? '');
            $altValues = site_image_alt_values($imageItem, 'svg', (string) $imageItem->svg, [
                'alt' => $fallbackAlt,
                'title' => $fallbackTitle,
            ]);

            return [
                'src' => image_normalize_url_scheme($imageItem->svg_url . '?v=' . $version),
                'type' => 'image/svg+xml',
                'src_webp' => null,
                'type_webp' => null,
                'alt' => $altValues['alt'],
                'title' => $altValues['title'],
            ];
        }

        $src      = !empty($imageItem->img_url) ? image_normalize_url_scheme($imageItem->img_url . '?v=' . $version) : null;
        $srcWebp  = !empty($imageItem->img_webp_url) ? image_normalize_url_scheme($imageItem->img_webp_url . '?v=' . $version) : null;
        $fallbackAlt = trim((string)($imageItem->img_alt ?? '')) !== ''
            ? (string) $imageItem->img_alt
            : (string)($options['alt'] ?? '');
        $fallbackTitle = trim((string)($imageItem->img_title ?? '')) !== ''
            ? (string) $imageItem->img_title
            : (string)($options['title'] ?? '');
        $altValues = site_image_alt_values($imageItem, 'img', (string) $imageItem->img, [
            'alt' => $fallbackAlt,
            'title' => $fallbackTitle,
        ]);

        if (!$src && $srcWebp) {
            return [
                'src' => $srcWebp,
                'type' => 'image/webp',
                'src_webp' => null,
                'type_webp' => null,
                'alt' => $altValues['alt'],
                'title' => $altValues['title'],
            ];
        }

        $result = [
            'src'       => $src ?: '',
            'type'      => $src ? (is_callable($mime) ? $mime($src) : null) : null,
            'src_webp'  => $srcWebp,
            'type_webp' => $srcWebp ? 'image/webp' : null,
            'alt'       => $altValues['alt'],
            'title'     => $altValues['title'],
        ];

        if ($fallbackOnEmpty && !$result['src'] && $fallback) {
            return $fallback;
        }

        return $result;
    }
}

if (!function_exists('site_image')) {
    /**
     * Get SiteImage by position name with optional default asset.
     *
     * @param string $positionName
     * @param string|null $fallbackRel Relative fallback asset path
     * @param array $options ['collection' => Collection, 'check_webp' => bool, 'fallback_on_empty' => bool]
     */
    function site_image(string $positionName, ?string $fallbackRel = null, array $options = []): array
    {
        static $cached;

        $collection = $options['collection'] ?? $options['siteImages'] ?? null;
        if (!$collection) {
            $cached = $cached ?? \App\Models\SiteImage::where('is_show', true)->get();
            $collection = $cached;
        }

        /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $collection */
        $item = $collection->firstWhere('position_name', $positionName);

        return site_image_from_item($item, $fallbackRel, $options);
    }
}

if (!function_exists('site_image_pair')) {
    /**
     * Convenience helper for desktop + mobile SiteImage pair.
     *
     * @param string $deskKey
     * @param string $mobKey
     * @param string|null $deskFallback
     * @param string|null $mobFallback
     * @param array $options Passed to site_image (e.g. ['collection' => $siteImages])
     */
    function site_image_pair(string $deskKey, string $mobKey, ?string $deskFallback = null, ?string $mobFallback = null, array $options = []): array
    {
        return [
            'desk' => site_image($deskKey, $deskFallback, $options),
            'mob'  => site_image($mobKey,  $mobFallback, $options),
        ];
    }
}

// TODO: Цена из базы с скобками
if (!function_exists('get_string_between')) {
    function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        if($len < 0){
            return explode(']',explode('[',$string)[1])[0];
        }
        return substr($string, $ini, $len);
    }
}

if (!function_exists('order_image_placeholder')) {
    function order_image_placeholder(?string $fallbackRel = null): string
    {
        $fallbackRel = $fallbackRel ?: (config('theme.current') . '/images/no_image.png');
        return asset($fallbackRel);
    }
}

if (!function_exists('order_image_local_path')) {
    function order_image_local_path(?string $raw): ?string
    {
        if (!$raw) {
            return null;
        }

        $path = parse_url($raw, PHP_URL_PATH);
        if (!$path) {
            $path = $raw;
        }

        $path = ltrim($path, '/');
        if ($path === '') {
            return null;
        }

        if (strpos($path, 'storage/user_images/') === 0) {
            $rel = substr($path, strlen('storage/'));
            return Storage::disk('public')->path($rel);
        }

        if (strpos($path, 'storage/gallery-items/') === 0) {
            $rel = substr($path, strlen('storage/'));
            return Storage::disk('public')->path($rel);
        }

        if (strpos($path, 'user_images/') === 0) {
            return Storage::disk('public')->path($path);
        }

        if (strpos($path, 'gallery-items/') === 0) {
            return Storage::disk('public')->path($path);
        }

        return public_path($path);
    }
}

if (!function_exists('order_image_exists')) {
    function order_image_exists(?string $raw): bool
    {
        if ($raw && (preg_match('#^https?://#i', $raw) || strpos($raw, '//') === 0)) {
            return true;
        }

        $localPath = order_image_local_path($raw);
        if (!$localPath) {
            return false;
        }

        return file_exists($localPath);
    }
}

if (!function_exists('order_image_url')) {
    function order_image_url(?string $raw, ?string $fallbackRel = null): string
    {
        if (!$raw || !order_image_exists($raw)) {
            return order_image_placeholder($fallbackRel);
        }

        if (preg_match('#^https?://#i', $raw) || strpos($raw, '//') === 0) {
            return $raw;
        }

        $path = parse_url($raw, PHP_URL_PATH);
        if (!$path) {
            $path = $raw;
        }

        $path = ltrim($path, '/');
        if (strpos($path, 'user_images/') === 0) {
            return asset('storage/' . $path);
        }

        if (strpos($path, 'gallery-items/') === 0) {
            return asset('storage/' . $path);
        }

        return asset($path);
    }
}

if (!function_exists('site_brand_same_as')) {
    /**
     * Return official social profiles used in Organization/brand schema.
     */
    function site_brand_same_as(): array
    {
        return array_values(array_filter([
            setting('sots-seti.facebook'),
            setting('sots-seti.instagram'),
        ]));
    }
}

if (!function_exists('site_brand_area_served_cities')) {
    /**
     * Build city list for areaServed from SeoCity records.
     */
    function site_brand_area_served_cities(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();

        return \App\Models\SeoCity::where('local', $locale)
            ->pluck('city')
            ->filter()
            ->unique()
            ->values()
            ->map(function ($city) {
                return ['@type' => 'City', 'name' => $city];
            })
            ->all();
    }
}

if (!function_exists('site_brand_contact_points')) {
    /**
     * Build reusable contact points for brand schema.
     *
     * @param array $phones
     * @param string|null $email
     * @param array|null $areaServed
     */
    function site_brand_contact_points(array $phones = [], ?string $email = 'orders@viarcanvas.com', ?array $areaServed = null): array
    {
        $areaServed = $areaServed ?? site_brand_area_served_cities();
        $contactPoints = [];

        foreach ($phones as $phone) {
            $phone = trim((string) $phone);

            if ($phone === '') {
                continue;
            }

            $contactPoints[] = array_filter([
                '@type' => 'ContactPoint',
                'telephone' => $phone,
                'email' => $email,
                'contactType' => 'Customer Service',
                'areaServed' => $areaServed,
            ], function ($value) {
                return $value !== null && $value !== [];
            });
        }

        return $contactPoints;
    }
}

if (!function_exists('site_brand_schema')) {
    /**
     * Build the shared brand schema.
     *
     * Supported overrides:
     * - @type
     * - @id
     * - name
     * - url
     * - logo
     * - sameAs
     * - contactPoint
     * - areaServed
     * - address
     * - department
     * - telephone
     * - legalName
     */
    function site_brand_schema(array $overrides = []): array
    {
        $sameAs = $overrides['sameAs'] ?? site_brand_same_as();
        $areaServed = $overrides['areaServed'] ?? site_brand_area_served_cities();
        $phones = $overrides['phones'] ?? [
            trans('header_footer_new.footer_phone_clean'),
            trans('header_footer_new.footer_phone_clean2'),
        ];
        $contactPoint = $overrides['contactPoint'] ?? site_brand_contact_points($phones, $overrides['email'] ?? 'orders@viarcanvas.com', $areaServed);

        $schema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => $overrides['@type'] ?? 'Organization',
            '@id' => $overrides['@id'] ?? rtrim(url('/'), '/') . '#organization',
            'name' => $overrides['name'] ?? trans('settings.site_name'),
            'url' => $overrides['url'] ?? url('/'),
            'logo' => $overrides['logo'] ?? asset('img/icons/logo.svg'),
            'sameAs' => !empty($sameAs) ? $sameAs : null,
            'telephone' => $overrides['telephone'] ?? null,
            'contactPoint' => !empty($contactPoint) ? $contactPoint : null,
            'areaServed' => !empty($areaServed) ? $areaServed : null,
            'address' => $overrides['address'] ?? null,
            'department' => $overrides['department'] ?? null,
            'legalName' => $overrides['legalName'] ?? null,
        ], function ($value) {
            return $value !== null && $value !== [];
        });

        return $schema;
    }
}

if (!function_exists('translated_value')) {
    function translated_value($item, string $key, $fallback = '')
    {
        if (is_object($item) && method_exists($item, 'getTranslatedAttribute')) {
            $value = $item->getTranslatedAttribute($key);
            if (is_string($value)) {
                $value = trim($value);
            }
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        $value = data_get($item, $key);
        if (is_string($value)) {
            $value = trim($value);
        }

        return $value !== null && $value !== '' ? $value : $fallback;
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return \TCG\Voyager\Facades\Voyager::setting($key, $default);
    }
}

if (! function_exists('menu')) {
    function menu(string $name, ?string $type = null, array $options = []): mixed
    {
        return \App\Models\Menu::display($name, $type, $options);
    }
}

if (! function_exists('voyager_asset')) {
    function voyager_asset(string $path): string
    {
        return asset('vendor/tcg/voyager/assets/'.ltrim($path, '/'));
    }
}
