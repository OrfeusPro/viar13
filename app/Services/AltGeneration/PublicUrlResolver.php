<?php

namespace App\Services\AltGeneration;

use App\Models\AllStylesPage;
use App\Models\BlogPost;
use App\Models\GalleryItem;
use App\Models\Page;
use App\Models\PageFaq;
use App\Models\SiteImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * Resolves public canonical URLs for configured alt-generation targets.
 */
class PublicUrlResolver
{
    /**
     * @param \App\Models\BlogPost $post
     * @return string|null
     */
    public static function blogPost(BlogPost $post): ?string
    {
        if (!self::isVisible($post, 'status', ['published', null])) {
            return null;
        }

        $slug = self::value($post, 'slug');

        return $slug ? self::route('blog_inner', ['slug' => trim($slug, '/')], '/blog/' . trim($slug, '/')) : null;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string|null
     */
    public static function blog(Model $model): ?string
    {
        return self::route('blog', [], '/blog');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string|null
     */
    public static function blogCategoryAll(Model $model): ?string
    {
        return self::route('blog_category_all', [], '/blog/category/all');
    }

    /**
     * @param \App\Models\GalleryItem $item
     * @return string|null
     */
    public static function galleryItem(GalleryItem $item): ?string
    {
        if (!self::isVisible($item, 'active', [1, '1', true])) {
            return null;
        }

        return self::trustedGalleryItemUrl($item);
    }

    /**
     * Resolve section/page URLs for GalleryItem MediaLibrary blocks.
     *
     * @param \App\Models\GalleryItem $item
     * @param string|null $field
     * @return string|null
     */
    public static function galleryItemMediaSection(GalleryItem $item, ?string $field): ?string
    {
        if (!self::isVisible($item, 'active', [1, '1', true]) || !self::isGalleryItemMediaField($field)) {
            return null;
        }

        $trusted = self::trustedGalleryItemUrl($item);

        if ($trusted !== null) {
            return $trusted;
        }

        $slug = self::value($item, 'slug');

        if ($slug === null || trim($slug) === '') {
            return null;
        }

        $slug = trim($slug, '/');

        if ($slug === 'simpsons-portrait') {
            return self::route('simpsons', [], null);
        }

        if ($slug === 'portrait-caricature' || $slug === 'caricature') {
            return self::route('caricature', [], null);
        }

        if ($slug === 'portrait-oil') {
            return self::route('graphic_portrait.oil', [], null);
        }

        if ($slug === 'portrait-historical') {
            return self::route('graphic_portrait.new_page--royal', [], null);
        }

        return self::route('graphic_portrait.new_page', ['slug' => $slug], null);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string|null
     */
    public static function gallery(Model $model): ?string
    {
        return self::route('gallery.index', [], '/gallery');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string|null
     */
    public static function canvas(Model $model): ?string
    {
        return self::route('canvas', [], '/new/canvas');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string|null
     */
    public static function canvasSlider(Model $model): ?string
    {
        $isSubCanvas = self::value($model, 'sub_canvas');

        return $isSubCanvas === '1'
            ? self::route('sizesprices', [], '/sizesprices')
            : self::canvas($model);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string|null
     */
    public static function portrait(Model $model): ?string
    {
        return self::route('graphic_portrait.new_page', ['slug' => 'portrait'], '/new/graphic-portrait/portrait');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string|null
     */
    public static function collage(Model $model): ?string
    {
        return self::route('collage', [], '/collage');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string|null
     */
    public static function contacts(Model $model): ?string
    {
        return self::route('contacts', [], '/page/contacts');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string|null
     */
    public static function home(Model $model): ?string
    {
        return self::route('home', [], '/');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string|null
     */
    public static function homeWhenShown(Model $model): ?string
    {
        return self::isShown($model) ? self::home($model) : null;
    }

    /**
     * @param \App\Models\AllStylesPage $item
     * @return string|null
     */
    public static function allStylesPage(AllStylesPage $item): ?string
    {
        if (!self::isShown($item)) {
            return null;
        }

        $isPhoto = self::value($item, 'is_photo');

        return $isPhoto === '1'
            ? self::route('photo_portrait', [], '/photo_portrait')
            : self::route('all_styles', [], '/all_styles');
    }

    /**
     * @param \App\Models\Page $page
     * @return string|null
     */
    public static function page(Page $page): ?string
    {
        $url = self::value($page, 'url') ?: self::value($page, 'slug');

        if (!$url) {
            return null;
        }

        $slug = trim($url, '/');

        if ($slug === '') {
            return self::home($page);
        }

        $routes = [
            'about' => ['about', '/about'],
            'contacts' => ['contacts', '/page/contacts'],
            'delivery' => ['delivery_page', '/page/delivery'],
            'faq' => ['faq', '/faq'],
            'condition' => ['condition', '/condition'],
            'review' => ['review', '/review'],
            'sizesprices' => ['sizesprices', '/sizesprices'],
            'all_styles' => ['all_styles', '/all_styles'],
            'photo_portrait' => ['photo_portrait', '/photo_portrait'],
            'new/canvas' => ['canvas', '/new/canvas'],
            'collage' => ['collage', '/collage'],
        ];

        if (isset($routes[$slug])) {
            return self::route($routes[$slug][0], [], $routes[$slug][1]);
        }

        return url('/page/' . $slug);
    }

    /**
     * @param \App\Models\PageFaq $faq
     * @return string|null
     */
    public static function pageFaq(PageFaq $faq): ?string
    {
        $page = self::value($faq, 'page');
        $keys = self::jsonKeys($page);

        $map = [
            'main' => ['home', '/'],
            'faq' => ['faq', '/faq'],
            'about' => ['about', '/about'],
            'contacts' => ['contacts', '/page/contacts'],
            'delivery' => ['delivery_page', '/page/delivery'],
            'review' => ['review', '/review'],
            'blog' => ['blog', '/blog'],
            'gallery_main' => ['gallery.index', '/gallery'],
            'prices_sizes' => ['sizesprices', '/sizesprices'],
            'gift_card' => ['gift_card_new', '/new/gift-card'],
            'canvas' => ['canvas', '/new/canvas'],
            'collage' => ['collage', '/collage'],
        ];

        foreach ($keys as $key) {
            if (isset($map[$key])) {
                return self::route($map[$key][0], [], $map[$key][1]);
            }
        }

        return self::route('faq', [], '/faq');
    }

    /**
     * @param \App\Models\SiteImage $image
     * @return string|null
     */
    public static function siteImage(SiteImage $image): ?string
    {
        if (!self::isShown($image)) {
            return null;
        }

        $page = self::value($image, 'page');

        if (!$page) {
            return null;
        }

        $map = [
            'main' => ['home', '/'],
            'home' => ['home', '/'],
            'index' => ['home', '/'],
            'about' => ['about', '/about'],
            'contacts' => ['contacts', '/page/contacts'],
            'delivery' => ['delivery_page', '/page/delivery'],
            'faq' => ['faq', '/faq'],
            'review' => ['review', '/review'],
            'blog' => ['blog', '/blog'],
            'gallery' => ['gallery.index', '/gallery'],
            'sizesprices' => ['sizesprices', '/sizesprices'],
            'condition' => ['condition', '/condition'],
            'canvas' => ['canvas', '/new/canvas'],
            'collage' => ['collage', '/collage'],
            'gift_card' => ['gift_card_new', '/new/gift-card'],
        ];

        $key = trim($page, '/');

        if (!isset($map[$key])) {
            return null;
        }

        return self::route($map[$key][0], [], $map[$key][1]);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string|null
     */
    public static function reviewWhenActive(Model $model): ?string
    {
        return self::isVisible($model, 'active', [1, '1', true])
            ? self::route('review', [], '/review')
            : null;
    }

    /**
     * @param string $name
     * @param array<string, mixed> $parameters
     * @param string|null $fallbackPath
     * @return string|null
     */
    private static function route(string $name, array $parameters = [], ?string $fallbackPath = null): ?string
    {
        try {
            if (Route::has($name)) {
                return route($name, $parameters);
            }
        } catch (Throwable $exception) {
            return null;
        }

        return $fallbackPath === null ? null : url($fallbackPath);
    }

    /**
     * @param \App\Models\GalleryItem $item
     * @return string|null
     */
    private static function trustedGalleryItemUrl(GalleryItem $item): ?string
    {
        $url = self::value($item, 'meta_url');

        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);
        $path = self::urlPath($url);
        $validationPath = $path === null ? null : self::stripLocalePrefix($path);

        if ($validationPath === null || self::isSuspiciousGalleryItemPath($validationPath)) {
            return null;
        }

        if (!self::isKnownPublicPath($validationPath)) {
            return null;
        }

        return preg_match('#^https?://#i', $url) ? $url : url('/' . ltrim($path, '/'));
    }

    /**
     * @param string|null $field
     * @return bool
     */
    private static function isGalleryItemMediaField(?string $field): bool
    {
        if ($field === null || strpos($field, 'media:') !== 0) {
            return false;
        }

        $collection = substr($field, 6);

        return in_array($collection, [
            'new_prices_sizes',
            'new_sizes',
            'works_examples_new',
            'our_works_new',
            'background',
            'reason_example',
            'new_before_items',
            'new_after_items',
            'new_clients_works',
        ], true);
    }

    /**
     * @param string $url
     * @return string|null
     */
    private static function urlPath(string $url): ?string
    {
        $path = preg_match('#^https?://#i', $url)
            ? parse_url($url, PHP_URL_PATH)
            : $url;

        if (!is_string($path) || trim($path) === '') {
            return null;
        }

        $parts = preg_split('/[?#]/', $path, 2);
        $path = '/' . ltrim((string) ($parts[0] ?? $path), '/');

        return $path;
    }

    /**
     * @param string $path
     * @return string
     */
    private static function stripLocalePrefix(string $path): string
    {
        $segments = explode('/', trim($path, '/'));
        $first = $segments[0] ?? '';

        if (in_array($first, ['ru', 'en', 'de', 'lv', 'lt', 'pl', 'ee'], true)) {
            array_shift($segments);
        }

        return empty($segments) ? '/' : '/' . implode('/', $segments);
    }

    /**
     * @param string $path
     * @return bool
     */
    private static function isSuspiciousGalleryItemPath(string $path): bool
    {
        $path = strtolower($path);

        return strpos($path, '/item/') !== false;
    }

    /**
     * @param string $path
     * @return bool
     */
    private static function isKnownPublicPath(string $path): bool
    {
        $path = '/' . ltrim($path, '/');

        foreach ([
            '/',
            '/new/canvas',
            '/new/gallery',
            '/new/caricature',
            '/simpsons',
            '/collage',
            '/gallery',
            '/blog',
            '/about',
            '/faq',
            '/review',
            '/sizesprices',
            '/all_styles',
            '/photo_portrait',
            '/condition',
            '/page/contacts',
            '/page/delivery',
        ] as $exactPath) {
            if ($path === $exactPath) {
                return true;
            }
        }

        foreach ([
            '/new/graphic-portrait/',
            '/new/caricature/',
            '/blog/',
            '/page/',
        ] as $prefix) {
            if (strpos($path, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $field
     * @return string|null
     */
    private static function value(Model $model, string $field): ?string
    {
        if (!array_key_exists($field, $model->getAttributes())) {
            return null;
        }

        try {
            if (method_exists($model, 'getTranslatedAttribute')) {
                $translated = $model->getTranslatedAttribute($field);

                if (is_scalar($translated) && trim((string) $translated) !== '') {
                    return trim((string) $translated);
                }
            }
        } catch (Throwable $exception) {
            // Read the raw value below when a translation is not available.
        }

        $value = $model->getAttribute($field);

        if ($value === null) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return $encoded === false ? null : $encoded;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool
     */
    private static function isShown(Model $model): bool
    {
        foreach (['is_show', 'active'] as $field) {
            if (!array_key_exists($field, $model->getAttributes())) {
                continue;
            }

            return self::isVisible($model, $field, [1, '1', true]);
        }

        return true;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $field
     * @param array<int, mixed> $visibleValues
     * @return bool
     */
    private static function isVisible(Model $model, string $field, array $visibleValues): bool
    {
        if (!array_key_exists($field, $model->getAttributes())) {
            return true;
        }

        $value = $model->getAttribute($field);

        foreach ($visibleValues as $visible) {
            if ($value === $visible || (string) $value === (string) $visible) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|null $value
     * @return array<int, string>
     */
    private static function jsonKeys(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return [trim($value, '/" ')];
        }

        $keys = [];

        foreach ($decoded as $key => $item) {
            if ($item !== null && $item !== '' && $item !== false) {
                $keys[] = (string) $key;
            }
        }

        return $keys;
    }
}
