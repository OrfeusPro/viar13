<?php

namespace App\Services\AltGeneration;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Collects direct and embedded image references from configured model fields.
 */
class ImageCollector
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \Illuminate\Contracts\Routing\UrlGenerator
     */
    private $url;

    /**
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Contracts\Routing\UrlGenerator $url
     */
    public function __construct(ConfigRepository $config, UrlGenerator $url)
    {
        $this->config = $config;
        $this->url = $url;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @return \Illuminate\Support\Collection<int, \App\Services\AltGeneration\ImageDescriptor>
     */
    public function collect(Model $entity, ?string $locale = null): Collection
    {
        $target = $this->targetFor($entity);

        if (isset($target['enabled']) && $target['enabled'] === false) {
            return new Collection();
        }

        $images = [];
        $seen = [];

        foreach ((array) ($target['image_fields'] ?? []) as $field) {
            if (!is_string($field) || $field === '') {
                continue;
            }

            foreach ($this->extractPaths($this->readAttribute($entity, $field, $locale)) as $path) {
                $descriptor = $this->descriptorFromPath($path, $field, null, null, 'field');
                $this->addDescriptor($descriptor, $images, $seen);
            }
        }

        foreach ((array) ($target['gallery_fields'] ?? []) as $field) {
            if (!is_string($field) || $field === '') {
                continue;
            }

            foreach ($this->extractPaths($this->readAttribute($entity, $field, $locale)) as $path) {
                $descriptor = $this->descriptorFromPath($path, $field, null, null, 'field');
                $this->addDescriptor($descriptor, $images, $seen);
            }
        }

        foreach ((array) ($target['media_collections'] ?? []) as $collection) {
            if (!is_string($collection) || $collection === '') {
                continue;
            }

            foreach ($this->mediaItems($entity, $collection) as $media) {
                $descriptor = $this->descriptorFromMedia($media, $collection, $locale);
                $this->addDescriptor($descriptor, $images, $seen);
            }
        }

        foreach ((array) ($target['html_fields'] ?? []) as $field) {
            if (!is_string($field) || $field === '') {
                continue;
            }

            $html = $this->readAttribute($entity, $field, $locale);

            if ($html === null || trim($html) === '') {
                continue;
            }

            foreach ($this->extractHtmlImages($html, $field) as $descriptor) {
                $this->addDescriptor($descriptor, $images, $seen);
            }
        }

        return new Collection($images);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @return array<string, mixed>
     */
    private function targetFor(Model $entity): array
    {
        $targets = (array) $this->config->get('alt_generation.targets', []);
        $entityClass = get_class($entity);

        if (isset($targets[$entityClass]) && is_array($targets[$entityClass])) {
            return $targets[$entityClass];
        }

        foreach ($targets as $class => $target) {
            if (is_string($class) && $entity instanceof $class && is_array($target)) {
                return $target;
            }
        }

        return [];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param string $field
     * @return mixed
     */
    private function readAttribute(Model $entity, string $field, ?string $locale = null)
    {
        if ($locale !== null && method_exists($entity, 'getTranslatedAttribute')) {
            try {
                $translated = $entity->getTranslatedAttribute($field, $locale, $this->config->get('app.fallback_locale'));

                if ($translated !== null && trim((string) $translated) !== '') {
                    return $translated;
                }
            } catch (\Throwable $exception) {
                // Fall back to the base attribute below.
            }
        }

        if (!array_key_exists($field, $entity->getAttributes())) {
            return null;
        }

        return $entity->getAttribute($field);
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private function extractPaths($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $this->extractPathsFromArray($value);
        }

        if (is_object($value)) {
            return $this->extractPathsFromArray((array) $value);
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $this->extractPathsFromArray($decoded);
        }

        return [$raw];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param string $collection
     * @return iterable<int, mixed>
     */
    private function mediaItems(Model $entity, string $collection): iterable
    {
        if (!method_exists($entity, 'getMedia')) {
            return [];
        }

        try {
            $items = $entity->getMedia($collection);
        } catch (\Throwable $exception) {
            return [];
        }

        return is_iterable($items) ? $items : [];
    }

    /**
     * @param mixed $media
     * @param string $collection
     * @param string|null $locale
     * @return \App\Services\AltGeneration\ImageDescriptor|null
     */
    private function descriptorFromMedia($media, string $collection, ?string $locale): ?ImageDescriptor
    {
        if (!is_object($media) || !method_exists($media, 'getUrl')) {
            return null;
        }

        try {
            $path = (string) $media->getUrl();
        } catch (\Throwable $exception) {
            return null;
        }

        if (trim($path) === '') {
            return null;
        }

        $locale = $locale !== null && trim($locale) !== ''
            ? $locale
            : (string) $this->config->get('app.locale', 'ru');
        $altKey = 'image_alt_' . $locale;
        $titleKey = 'image_title_' . $locale;
        $collectionName = isset($media->collection_name) && is_string($media->collection_name)
            ? $media->collection_name
            : $collection;
        $mediaId = isset($media->id) ? (int) $media->id : null;

        return $this->descriptorFromPath(
            $path,
            'media:' . $collection,
            $this->mediaCustomProperty($media, $altKey),
            $this->mediaCustomProperty($media, $titleKey),
            'media',
            null,
            [
                'media_id' => $mediaId,
                'collection_name' => $collectionName,
                'custom_alt_key' => $altKey,
                'custom_title_key' => $titleKey,
            ]
        );
    }

    /**
     * @param mixed $media
     * @param string $key
     * @return string|null
     */
    private function mediaCustomProperty($media, string $key): ?string
    {
        if (!is_object($media) || !method_exists($media, 'getCustomProperty')) {
            return null;
        }

        try {
            $value = $media->getCustomProperty($key);
        } catch (\Throwable $exception) {
            return null;
        }

        if ($value === null || is_array($value) || is_object($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param array<mixed> $items
     * @return array<int, string>
     */
    private function extractPathsFromArray(array $items): array
    {
        $paths = [];

        foreach ($items as $item) {
            if (is_string($item) && trim($item) !== '') {
                $paths[] = $item;
                continue;
            }

            if (!is_array($item)) {
                continue;
            }

            foreach (['download_link', 'path', 'url', 'src', 'image', 'img'] as $key) {
                if (isset($item[$key]) && is_string($item[$key]) && trim($item[$key]) !== '') {
                    $paths[] = $item[$key];
                    continue 2;
                }
            }

            $paths = array_merge($paths, $this->extractPathsFromArray($item));
        }

        return $paths;
    }

    /**
     * @param string $html
     * @param string $field
     * @return array<int, \App\Services\AltGeneration\ImageDescriptor>
     */
    private function extractHtmlImages(string $html, string $field): array
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="utf-8" ?>' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $nodes = $xpath->query('//img');
        $images = [];

        if ($nodes === false) {
            return [];
        }

        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $src = $node->getAttribute('src');
            $descriptor = $this->descriptorFromPath(
                $src,
                $field,
                $node->hasAttribute('alt') ? $node->getAttribute('alt') : null,
                $node->hasAttribute('title') ? $node->getAttribute('title') : null,
                'html',
                $this->nodeXPath($node)
            );

            if ($descriptor !== null) {
                $images[] = $descriptor;
            }
        }

        return $images;
    }

    /**
     * @param string $path
     * @param string|null $field
     * @param string|null $currentAlt
     * @param string|null $currentTitle
     * @param string $sourceType
     * @param string|null $xpath
     * @param array<string, mixed> $meta
     * @return \App\Services\AltGeneration\ImageDescriptor|null
     */
    private function descriptorFromPath(
        string $path,
        ?string $field,
        ?string $currentAlt,
        ?string $currentTitle,
        string $sourceType,
        ?string $xpath = null,
        array $meta = []
    ): ?ImageDescriptor {
        $normalized = $this->normalizePath($path);

        if ($normalized === null) {
            return null;
        }

        $absolutePath = $this->absolutePathFor($normalized);

        if ($this->shouldExclude($normalized, $absolutePath)) {
            return null;
        }

        return new ImageDescriptor(
            $normalized,
            $field,
            $currentAlt,
            $currentTitle,
            $sourceType,
            $xpath,
            $absolutePath,
            $this->publicUrlFor($normalized),
            $meta
        );
    }

    /**
     * @param string $path
     * @return string|null
     */
    private function normalizePath(string $path): ?string
    {
        $value = trim(html_entity_decode($path, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $value = trim($value, "\"' \t\n\r\0\x0B");

        if ($value === '' || strpos($value, '#') === 0) {
            return null;
        }

        if (strpos($value, 'data:image/') === 0) {
            return $value;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $this->normalizeUrlPath($value);
        }

        $withoutQuery = preg_split('/[?#]/', $value, 2);
        $value = str_replace('\\', '/', $withoutQuery[0] ?? $value);
        $value = preg_replace('#/{2,}#', '/', $value);
        $value = ltrim((string) $value, '/');

        if (strpos($value, 'public/storage/') === 0) {
            return substr($value, 15);
        }

        if (strpos($value, 'storage/') === 0) {
            return substr($value, 8);
        }

        return $value === '' ? null : $value;
    }

    /**
     * @param string $url
     * @return string
     */
    private function normalizeUrlPath(string $url): string
    {
        $appUrl = (string) $this->config->get('app.url', '');
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);

        if ($appHost !== null && $urlHost !== null && strcasecmp((string) $appHost, (string) $urlHost) === 0) {
            $path = (string) parse_url($url, PHP_URL_PATH);

            if (strpos($path, '/storage/') === 0) {
                return ltrim(substr($path, 9), '/');
            }

            return ltrim($path, '/');
        }

        return $url;
    }

    /**
     * @param string $path
     * @return string|null
     */
    private function absolutePathFor(string $path): ?string
    {
        if ($this->isRemoteOrDataPath($path)) {
            return null;
        }

        if ($this->isPublicPath($path)) {
            return public_path($path);
        }

        $storagePath = storage_path('app/public/' . $path);

        if (is_file($storagePath)) {
            return $storagePath;
        }

        return public_path('storage/' . $path);
    }

    /**
     * @param string $path
     * @return string|null
     */
    private function publicUrlFor(string $path): ?string
    {
        if (strpos($path, 'data:image/') === 0) {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        if ($this->isPublicPath($path)) {
            return $this->url->to('/' . ltrim($path, '/'));
        }

        return $this->url->to('/storage/' . ltrim($path, '/'));
    }

    /**
     * @param string $path
     * @return bool
     */
    private function isPublicPath(string $path): bool
    {
        foreach (['uploads/', 'theme/', 'admin/', 'images/', 'img/', 'css/', 'js/'] as $prefix) {
            if (strpos($path, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $path
     * @return bool
     */
    private function isRemoteOrDataPath(string $path): bool
    {
        return strpos($path, 'data:image/') === 0 || preg_match('#^https?://#i', $path) === 1;
    }

    /**
     * @param string $path
     * @param string|null $absolutePath
     * @return bool
     */
    private function shouldExclude(string $path, ?string $absolutePath): bool
    {
        $cleanPath = $this->cleanPathForRules($path);

        foreach ((array) $this->config->get('alt_generation.exclude.path_prefixes', []) as $prefix) {
            if (!is_string($prefix) || trim($prefix) === '') {
                continue;
            }

            $prefix = strtolower(ltrim(str_replace('\\', '/', trim($prefix)), '/'));

            if (strpos($cleanPath, $prefix) === 0) {
                return true;
            }
        }

        foreach ((array) $this->config->get('alt_generation.exclude.extensions', []) as $extension) {
            if (!is_string($extension) || trim($extension) === '') {
                continue;
            }

            $extension = strtolower(trim($extension));
            $extension = strpos($extension, '.') === 0 ? $extension : '.' . $extension;

            if (substr($cleanPath, -strlen($extension)) === $extension) {
                return true;
            }
        }

        foreach ((array) $this->config->get('alt_generation.exclude.exclude_patterns', []) as $pattern) {
            if (!is_string($pattern) || trim($pattern) === '') {
                continue;
            }

            if (@preg_match($pattern, $cleanPath) === 1) {
                return true;
            }
        }

        return $this->fileSizeExcluded($absolutePath);
    }

    /**
     * @param string $path
     * @return string
     */
    private function cleanPathForRules(string $path): string
    {
        if (preg_match('#^https?://#i', $path)) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $withoutQuery = preg_split('/[?#]/', $path, 2);
        $path = str_replace('\\', '/', $withoutQuery[0] ?? $path);
        $path = preg_replace('#/{2,}#', '/', $path);

        return strtolower(ltrim((string) $path, '/'));
    }

    /**
     * @param string|null $absolutePath
     * @return bool
     */
    private function fileSizeExcluded(?string $absolutePath): bool
    {
        if ($absolutePath === null || !is_file($absolutePath)) {
            return false;
        }

        $size = @filesize($absolutePath);

        if ($size === false) {
            return false;
        }

        $min = (int) $this->config->get('alt_generation.exclude.file_size.min_bytes', 0);
        $max = (int) $this->config->get('alt_generation.exclude.file_size.max_bytes', 0);

        if ($min > 0 && $size < $min) {
            return true;
        }

        return $max > 0 && $size > $max;
    }

    /**
     * @param \App\Services\AltGeneration\ImageDescriptor|null $descriptor
     * @param array<int, \App\Services\AltGeneration\ImageDescriptor> $images
     * @param array<string, bool> $seen
     * @return void
     */
    private function addDescriptor(?ImageDescriptor $descriptor, array &$images, array &$seen): void
    {
        if ($descriptor === null) {
            return;
        }

        $key = $this->dedupeKey($descriptor);

        if (isset($seen[$key])) {
            return;
        }

        $seen[$key] = true;
        $images[] = $descriptor;
    }

    /**
     * @param \App\Services\AltGeneration\ImageDescriptor $descriptor
     * @return string
     */
    private function dedupeKey(ImageDescriptor $descriptor): string
    {
        $key = $descriptor->absolutePath ?: $descriptor->publicUrl ?: $descriptor->path;

        return strtolower((string) $descriptor->field . '|' . str_replace('\\', '/', $key));
    }

    /**
     * @param \DOMNode $node
     * @return string
     */
    private function nodeXPath(DOMNode $node): string
    {
        $segments = [];

        while ($node instanceof DOMElement) {
            $index = 1;
            $sibling = $node->previousSibling;

            while ($sibling !== null) {
                if ($sibling instanceof DOMElement && $sibling->nodeName === $node->nodeName) {
                    $index++;
                }

                $sibling = $sibling->previousSibling;
            }

            $segments[] = $node->nodeName . '[' . $index . ']';
            $node = $node->parentNode;
        }

        return '/' . implode('/', array_reverse($segments));
    }
}
