<?php

namespace App\Services\SeoMetaGeneration;

use App\Models\AGalleryAge;
use App\Models\AGalleryGenre;
use App\Models\AGalleryNationality;
use App\Models\AGalleryStyle;
use App\Models\AllStyle;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\GiftCard;
use App\Models\CanvasHeader;
use App\Models\CollageHeader;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\GalleryPage;
use App\Models\GalleryType;
use App\Models\HomepageOption;
use App\Models\ModularPicsHead;
use App\Models\Page;
use App\Models\PageDelivery;
use App\Models\PagePartnership;
use App\Models\Stock;
use App\Services\AltGeneration\PublicUrlResolver;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;

class SeoMetaContextResolver
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    public function __construct(ConfigRepository $config)
    {
        $this->config = $config;
    }

    /**
     * @return array<int, string>
     */
    public function supportedLocales(): array
    {
        foreach ([
                     'voyager.multilingual.locales',
                     'app.locales',
                     'translatable.locales',
                 ] as $key) {
            $configured = $this->config->get($key, []);

            if (is_string($configured)) {
                $configured = explode(',', $configured);
            }

            if (is_array($configured) && !empty($configured)) {
                return $this->normalizeLocaleList($configured);
            }
        }

        $locale = $this->normalizeLocale($this->config->get('app.locale'));

        return [$locale ?: 'en'];
    }

    public function defaultLocale(): string
    {
        // Public SEO URLs use Russian as the default locale in the sitemap.
        // Voyager multilingual config may still say "en", but SEO meta suggestions
        // must match public URLs: ru without prefix, every other locale with prefix.
        $configured = $this->normalizeLocale($this->config->get('seo_meta_generation.locale_fallback.default_locale'))
            ?: $this->normalizeLocale($this->config->get('app.locale'))
            ?: 'ru';

        $locales = $this->supportedLocales();

        return in_array($configured, $locales, true) ? $configured : 'ru';
    }

    /**
     * @param array<int, string> $locales
     * @return array<int, string>
     */
    public function normalizeRequestedLocales(array $locales): array
    {
        $supported = $this->supportedLocales();
        $normalized = [];

        foreach ($locales as $locale) {
            $value = $this->normalizeLocale($locale);

            if ($value !== null && in_array($value, $supported, true)) {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function targetForModel(string $modelClass): ?array
    {
        $targets = (array) $this->config->get('seo_meta_generation.targets', []);

        return isset($targets[$modelClass]) && is_array($targets[$modelClass]) ? $targets[$modelClass] : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function resolve(Model $entity, array $locales): array
    {
        $modelClass = get_class($entity);
        $target = $this->targetForModel($modelClass);

        if ($target === null) {
            throw new \InvalidArgumentException('Unsupported SEO meta model: ' . $modelClass);
        }

        $locales = $this->normalizeRequestedLocales($locales);

        if (empty($locales)) {
            throw new \InvalidArgumentException('No supported locales were requested.');
        }

        $firstLocale = $locales[0];
        $context = [
            'brand' => 'ViarCanvas / Viar',
            'site' => 'custom wall art and personalized gifts ecommerce site',
            'task' => 'Generate specific commercial SEO meta from entity data. If current meta is empty, use source fields and facts instead of generic copy.',
            'entity' => $this->entityContext($entity, $target, $firstLocale),
            'locales' => [],
        ];

        foreach ($locales as $locale) {
            $localeContext = $this->localeContext($entity, $target, $locale);

            if (!empty($localeContext)) {
                $context['locales'][$locale] = $localeContext;
            }
        }

        return $this->trimTotalContext($context);
    }

    public function compressedJson(array $context): string
    {
        $json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $json === false ? '{}' : $json;
    }

    /**
     * @return array<string, mixed>
     */
    private function entityContext(Model $entity, array $target, string $locale): array
    {
        $context = [
            'type' => (string) ($target['label'] ?? class_basename($entity)),
            'model' => get_class($entity),
            'id' => (int) $entity->getKey(),
            'page_type' => (string) ($target['page_type'] ?? 'page'),
            'product_type' => (string) ($target['product_type'] ?? ''),
            'primary_action' => (string) ($target['primary_action'] ?? ''),
        ];

        $url = $this->resolvePublicUrl($entity, $locale);
        $canonicalUrl = $this->resolvePublicUrl($entity, $this->defaultLocale());
        $slug = $this->firstFieldValue($entity, (array) ($target['context_fields']['slug'] ?? []), $locale, true);

        if ($url !== null) {
            $context['url'] = $url;
            $context['url_locale'] = $locale;
        }

        if ($canonicalUrl !== null && $canonicalUrl !== $url) {
            $context['canonical_url'] = $canonicalUrl;
        }

        if ($slug !== null) {
            $context['slug'] = $slug;
        }

        return array_filter($context, static function ($value): bool {
            return !($value === null || $value === '');
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function localeContext(Model $entity, array $target, string $locale): array
    {
        $fields = (array) ($target['context_fields'] ?? []);
        $titleField = (string) $target['title_field'];
        $descriptionField = (string) $target['description_field'];
        $defaultLocale = $this->defaultLocale();

        $sourceTitle = $this->firstFieldValueWithSource($entity, (array) ($fields['title'] ?? []), $locale, $defaultLocale);
        $sourceDescription = $this->firstFieldValueWithSource($entity, (array) ($fields['description'] ?? []), $locale, $defaultLocale);
        $body = $this->firstFieldValueWithSource($entity, (array) ($fields['body'] ?? []), $locale, $defaultLocale);
        $currentMetaTitle = $this->readAttribute($entity, $titleField, $locale, false);
        $currentMetaDescription = $this->readAttribute($entity, $descriptionField, $locale, false);
        $facts = $this->fieldFacts($entity, (array) ($fields['facts'] ?? []), $locale, $defaultLocale);
        $pageUrl = $this->resolvePublicUrl($entity, $locale);

        $context = [
            'language' => $locale,
            'source_locale' => $this->sourceLocaleFromParts([$sourceTitle, $sourceDescription, $body], $locale),
            'page_url' => $pageUrl,
            'source_title' => $this->cleanLimited($sourceTitle['value'] ?? null, 'field_max_chars'),
            'source_description' => $this->cleanLimited($sourceDescription['value'] ?? null, 'field_max_chars'),
            'body_excerpt' => $this->cleanLimited($body['value'] ?? null, 'body_excerpt_max_chars'),
            'current_meta_title' => $this->cleanLimited($currentMetaTitle, 'field_max_chars'),
            'current_meta_description' => $this->cleanLimited($currentMetaDescription, 'field_max_chars'),
            'facts' => $facts,
            'instructions' => [
                'If current_meta_title/current_meta_description are empty, generate from source_title, source_description, body_excerpt, facts, and entity fields.',
                'Do not use generic marketing filler. Keep the main keyword and concrete selling facts.',
            ],
        ];

        $deduped = [];
        $seen = [];

        foreach ($context as $key => $value) {
            if (is_array($value)) {
                if (!empty($value)) {
                    $deduped[$key] = $value;
                }
                continue;
            }

            $value = is_string($value) ? $this->cleanText($value) : $value;

            if ($value === null || $value === '') {
                continue;
            }

            if (is_string($value) && in_array($key, ['source_title', 'source_description', 'body_excerpt', 'current_meta_title', 'current_meta_description'], true)) {
                $signature = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);

                if (isset($seen[$signature])) {
                    continue;
                }

                $seen[$signature] = true;
            }

            $deduped[$key] = $value;
        }

        return $deduped;
    }

    /**
     * @param array<int, array{value: string|null, locale: string|null}> $parts
     */
    private function sourceLocaleFromParts(array $parts, string $requestedLocale): ?string
    {
        foreach ($parts as $part) {
            if (($part['value'] ?? null) !== null && ($part['locale'] ?? null) !== null) {
                return (string) $part['locale'];
            }
        }

        return $requestedLocale;
    }

    /**
     * @param array<int, string> $fields
     * @return array<string, string>
     */
    private function fieldFacts(Model $entity, array $fields, string $locale, string $defaultLocale): array
    {
        $facts = [];

        foreach ($fields as $field) {
            if (!is_string($field) || $field === '') {
                continue;
            }

            $value = $this->readAttributeWithLocaleFallback($entity, $field, $locale, $defaultLocale);
            $clean = $this->cleanText($value['value'] ?? null);

            if ($clean === null) {
                continue;
            }

            $facts[$field] = $this->truncate($clean, (int) $this->config->get('seo_meta_generation.limits.fact_max_chars', 160));
        }

        return $facts;
    }

    /**
     * @param array<int, string> $fields
     */
    private function firstFieldValue(Model $entity, array $fields, string $locale, bool $fallback): ?string
    {
        foreach ($fields as $field) {
            if (!is_string($field) || $field === '') {
                continue;
            }

            $value = $this->readAttribute($entity, $field, $locale, $fallback);

            if ($this->cleanText($value) !== null) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $fields
     * @return array{value: string|null, locale: string|null}
     */
    private function firstFieldValueWithSource(Model $entity, array $fields, string $locale, string $defaultLocale): array
    {
        foreach ($fields as $field) {
            if (!is_string($field) || $field === '') {
                continue;
            }

            $value = $this->readAttributeWithLocaleFallback($entity, $field, $locale, $defaultLocale);

            if ($this->cleanText($value['value'] ?? null) !== null) {
                return $value;
            }
        }

        return ['value' => null, 'locale' => null];
    }

    /**
     * @return array{value: string|null, locale: string|null}
     */
    private function readAttributeWithLocaleFallback(Model $entity, string $field, string $locale, string $defaultLocale): array
    {
        $value = $this->readAttribute($entity, $field, $locale, false);

        if ($this->cleanText($value) !== null) {
            return ['value' => $value, 'locale' => $locale];
        }

        if ($defaultLocale !== $locale) {
            $value = $this->readAttribute($entity, $field, $defaultLocale, false);

            if ($this->cleanText($value) !== null) {
                return ['value' => $value, 'locale' => $defaultLocale];
            }
        }

        $value = $this->readAttribute($entity, $field, $locale, true);

        if ($this->cleanText($value) !== null) {
            return ['value' => $value, 'locale' => $defaultLocale];
        }

        return ['value' => null, 'locale' => null];
    }

    private function readAttribute(Model $entity, string $field, string $locale, bool $fallback): ?string
    {
        if (method_exists($entity, 'getTranslatedAttribute')) {
            try {
                $translated = $entity->getTranslatedAttribute($field, $locale, $fallback);

                if ($translated !== null && trim((string) $translated) !== '') {
                    return (string) $translated;
                }
            } catch (\Throwable $exception) {
            }
        }

        if (!array_key_exists($field, $entity->getAttributes())) {
            return null;
        }

        $value = $entity->getAttribute($field);

        if ($value === null) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return $encoded === false ? null : $encoded;
        }

        return (string) $value;
    }

    private function resolvePublicUrl(Model $entity, string $locale): ?string
    {
        $target = $this->targetForModel(get_class($entity));

        if ($target !== null) {
            $configuredUrl = $this->configuredUrl($entity, $target, $locale);

            if ($configuredUrl !== null) {
                return $this->localizePublicUrl($configuredUrl, $locale);
            }
        }

        $url = null;

        if ($entity instanceof BlogPost) {
            $url = PublicUrlResolver::blogPost($entity);
        } elseif ($entity instanceof BlogCategory) {
            $url = $this->pathFromFields('/blog/category/{slug}', $entity, $locale);
        } elseif ($entity instanceof GalleryItem) {
            $url = $this->galleryItemUrl($entity, $locale) ?: PublicUrlResolver::galleryItem($entity);
        } elseif ($entity instanceof GalleryPage) {
            $url = '/new/gallery';
        } elseif ($entity instanceof GalleryCategory) {
            $url = $this->pathFromFields('/gallery/{gallery_type}/{url}', $entity, $locale);
        } elseif ($entity instanceof AGalleryGenre) {
            $url = $this->pathFromFields('/gallery/genre/{alias}', $entity, $locale);
        } elseif ($entity instanceof AGalleryStyle) {
            $url = $this->pathFromFields('/gallery/style/{alias}', $entity, $locale);
        } elseif ($entity instanceof AGalleryAge) {
            $url = $this->pathFromFields('/gallery/age/{alias}', $entity, $locale);
        } elseif ($entity instanceof AGalleryNationality) {
            $url = $this->pathFromFields('/gallery/nationality/{alias}', $entity, $locale);
        } elseif ($entity instanceof CanvasHeader) {
            $url = '/new/canvas';
        } elseif ($entity instanceof CollageHeader) {
            $url = '/collage';
        } elseif ($entity instanceof Stock) {
            $url = '/stocks';
        } elseif ($entity instanceof GiftCard) {
            $url = '/new/gift-card';
        } elseif ($entity instanceof ModularPicsHead) {
            $url = '/modular-generator';
        } elseif ($entity instanceof HomepageOption) {
            $url = '/';
        } elseif ($entity instanceof AllStyle) {
            $url = '/all_styles';
        } elseif ($entity instanceof PageDelivery) {
            $url = '/page/delivery';
        } elseif ($entity instanceof PagePartnership) {
            $url = '/page/partnership';
        } elseif ($entity instanceof Page) {
            $url = PublicUrlResolver::page($entity);
        }

        return $this->localizePublicUrl($url, $locale);
    }

    /**
     * @param array<string, mixed> $target
     */
    private function configuredUrl(Model $entity, array $target, string $locale): ?string
    {
        $path = $target['url_path'] ?? null;

        if (!is_string($path) || trim($path) === '') {
            return null;
        }

        return $this->pathFromFields($path, $entity, $locale);
    }

    private function pathFromFields(string $template, Model $entity, string $locale): ?string
    {
        $path = trim($template);

        if ($path === '') {
            return null;
        }

        if (!preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $path, $matches)) {
            return $path;
        }

        foreach ($matches[1] as $placeholder) {
            $value = $this->urlPlaceholderValue($entity, $placeholder, $locale);

            if ($value === null || trim($value) === '') {
                return null;
            }

            $path = str_replace('{' . $placeholder . '}', rawurlencode(trim($value, '/')), $path);
        }

        return $path;
    }

    private function urlPlaceholderValue(Model $entity, string $placeholder, string $locale): ?string
    {
        if ($placeholder === 'id') {
            return (string) $entity->getKey();
        }

        if ($placeholder === 'gallery_type' || $placeholder === 'type') {
            return $this->galleryTypeSlugForEntity($entity, $locale);
        }

        $fields = [$placeholder];

        if ($placeholder === 'slug') {
            $fields = ['slug', 'url', 'alias', 'meta_url'];
        }

        foreach ($fields as $field) {
            $value = $this->readAttribute($entity, $field, $locale, true);

            if ($this->cleanText($value) !== null) {
                $path = $this->urlPath((string) $value);

                if ($field === 'meta_url' && $path !== null) {
                    $segments = explode('/', trim($this->stripLocalePrefix($path), '/'));
                    $last = end($segments);

                    return is_string($last) && trim($last) !== '' ? $last : null;
                }

                return trim((string) $value, '/');
            }
        }

        return null;
    }

    private function galleryTypeSlugForEntity(Model $entity, string $locale): ?string
    {
        $idType = array_key_exists('id_type', $entity->getAttributes()) ? $entity->getAttribute('id_type') : null;

        if ($idType !== null && trim((string) $idType) !== '') {
            $type = GalleryType::query()->where('id', (int) $idType)->first();

            if ($type instanceof GalleryType) {
                $translated = $this->readAttribute($type, 'url', $locale, true);

                if ($this->cleanText($translated) !== null) {
                    return trim((string) $translated, '/');
                }
            }
        }

        if ($entity instanceof GalleryItem) {
            return $this->galleryTypeSlug($entity);
        }

        $metaUrl = $this->readAttribute($entity, 'meta_url', $locale, true);
        $path = $metaUrl === null ? null : $this->stripLocalePrefix((string) $this->urlPath($metaUrl));

        if ($path !== null && preg_match('#^/gallery/([^/]+)(?:/|$)#i', $path, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function galleryItemUrl(GalleryItem $item, string $locale): ?string
    {
        if (array_key_exists('active', $item->getAttributes())) {
            $active = $item->getAttribute('active');

            if (!($active === 1 || $active === '1' || $active === true)) {
                return null;
            }
        }

        $specialPath = $this->specialGalleryItemPath($item, $locale);

        if ($specialPath !== null) {
            return $specialPath;
        }

        $metaUrl = $this->readAttribute($item, 'meta_url', $locale, true);

        if ($metaUrl !== null && trim($metaUrl) !== '') {
            $path = $this->urlPath($metaUrl);
            $pathWithoutLocale = $path === null ? null : $this->stripLocalePrefix($path);

            if ($pathWithoutLocale !== null) {
                if (strpos($pathWithoutLocale, '/gallery/') === 0 && strpos($pathWithoutLocale, '/item/') !== false) {
                    return $pathWithoutLocale;
                }

                if (strpos($pathWithoutLocale, '/new/graphic-portrait/') === 0
                    || strpos($pathWithoutLocale, '/new/caricature') === 0
                    || $pathWithoutLocale === '/simpsons') {
                    return $pathWithoutLocale;
                }
            }
        }

        $typeSlug = $this->galleryTypeSlug($item);

        if ($typeSlug === null) {
            return null;
        }

        return '/gallery/' . trim($typeSlug, '/') . '/item/' . (int) $item->getKey();
    }

    private function specialGalleryItemPath(GalleryItem $item, string $locale): ?string
    {
        $id = (int) $item->getKey();
        $slug = $this->readAttribute($item, 'slug', $locale, true)
            ?: $this->readAttribute($item, 'slug', $this->defaultLocale(), true);
        $slug = $slug === null ? null : strtolower(trim((string) $slug, '/'));
        $metaUrl = $this->readAttribute($item, 'meta_url', $locale, true)
            ?: $this->readAttribute($item, 'meta_url', $this->defaultLocale(), true);
        $metaPath = $metaUrl === null ? null : $this->stripLocalePrefix((string) $this->urlPath($metaUrl));

        if ($id === 1055 || $slug === 'simpsons-portrait' || $metaPath === '/simpsons') {
            return '/simpsons';
        }

        if ($id === 1058 || $slug === 'caricature' || $metaPath === '/new/caricature') {
            return '/new/caricature';
        }

        if ($id === 18 || $slug === 'portrait-caricature' || $metaPath === '/new/graphic-portrait/portrait-caricature') {
            return '/new/graphic-portrait/portrait-caricature';
        }

        return null;
    }

    private function galleryTypeSlug(GalleryItem $item): ?string
    {
        $idType = array_key_exists('id_type', $item->getAttributes()) ? $item->getAttribute('id_type') : null;

        if ($idType !== null && trim((string) $idType) !== '') {
            $url = GalleryType::query()->where('id', (int) $idType)->value('url');

            if (is_string($url) && trim($url) !== '') {
                return trim($url, '/');
            }
        }

        $metaUrl = $this->readAttribute($item, 'meta_url', $this->defaultLocale(), true);
        $path = $metaUrl === null ? null : $this->stripLocalePrefix((string) $this->urlPath($metaUrl));

        if ($path !== null && preg_match('#^/gallery/([^/]+)/item/\d+#i', $path, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function localizePublicUrl(?string $url, string $locale): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);
        $parts = parse_url($url);
        $path = isset($parts['path']) ? (string) $parts['path'] : $url;
        $path = '/' . ltrim($path, '/');
        $pathWithoutLocale = $this->stripLocalePrefix($path);
        $defaultLocale = $this->defaultLocale();
        $needsPrefix = $locale !== $defaultLocale;
        $localizedPath = ($needsPrefix ? '/' . $locale : '') . ($pathWithoutLocale === '/' ? '' : $pathWithoutLocale);
        $localizedPath = $localizedPath === '' ? '/' : $localizedPath;

        $appUrl = (string) $this->config->get('app.url', '');
        $appParts = parse_url($appUrl);
        $scheme = isset($appParts['scheme']) ? (string) $appParts['scheme'] : 'https';
        $host = isset($appParts['host']) && (string) $appParts['host'] !== ''
            ? (string) $appParts['host']
            : (isset($parts['host']) ? (string) $parts['host'] : null);
        $port = isset($appParts['port']) ? ':' . (string) $appParts['port'] : '';
        $query = isset($parts['query']) ? '?' . (string) $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . (string) $parts['fragment'] : '';

        if ($host === null || trim($host) === '') {
            return url($localizedPath);
        }

        return $scheme . '://' . $host . $port . $localizedPath . $query . $fragment;
    }

    private function stripLocalePrefix(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $segments = explode('/', trim($path, '/'));
        $first = $segments[0] ?? '';

        if ($first !== '' && in_array($first, $this->supportedLocales(), true)) {
            array_shift($segments);

            return empty($segments) ? '/' : '/' . implode('/', $segments);
        }

        return $path;
    }

    private function urlPath(string $url): ?string
    {
        $path = preg_match('#^https?://#i', $url) ? parse_url($url, PHP_URL_PATH) : $url;

        if (!is_string($path) || trim($path) === '') {
            return null;
        }

        $parts = preg_split('/[?#]/', $path, 2);
        $path = '/' . ltrim((string) ($parts[0] ?? $path), '/');

        return $path;
    }

    private function cleanLimited(?string $value, string $limitKey): ?string
    {
        $clean = $this->cleanText($value);

        if ($clean === null) {
            return null;
        }

        return $this->truncate($clean, (int) $this->config->get('seo_meta_generation.limits.' . $limitKey, 300));
    }

    private function cleanText(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = str_ireplace(['<br>', '<br/>', '<br />'], ' ', $value);
        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim((string) $text);

        return $text === '' ? null : $text;
    }

    private function truncate(string $value, int $limit): string
    {
        if ($limit <= 0) {
            return $value;
        }

        if (function_exists('mb_strlen') && mb_strlen($value, 'UTF-8') > $limit) {
            return mb_substr($value, 0, $limit, 'UTF-8');
        }

        if (!function_exists('mb_strlen') && strlen($value) > $limit) {
            return substr($value, 0, $limit);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function trimTotalContext(array $context): array
    {
        $limit = (int) $this->config->get('seo_meta_generation.limits.total_context_max_chars', 7000);

        if ($limit <= 0 || strlen($this->compressedJson($context)) <= $limit) {
            return $context;
        }

        foreach ($context['locales'] as $locale => $localeContext) {
            if (isset($localeContext['body_excerpt'])) {
                $context['locales'][$locale]['body_excerpt'] = $this->truncate((string) $localeContext['body_excerpt'], 350);
            }
        }

        return $context;
    }

    /**
     * @param array<int|string, mixed> $locales
     * @return array<int, string>
     */
    private function normalizeLocaleList(array $locales): array
    {
        $normalized = [];

        foreach ($locales as $locale) {
            $value = $this->normalizeLocale(is_string($locale) ? $locale : null);

            if ($value !== null) {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function normalizeLocale($locale): ?string
    {
        if (!is_string($locale)) {
            return null;
        }

        $locale = strtolower(trim(str_replace('_', '-', $locale)));

        return $locale === '' ? null : $locale;
    }
}
