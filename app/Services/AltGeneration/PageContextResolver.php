<?php

namespace App\Services\AltGeneration;

use App\Models\ImageAltSuggestion;
use App\Models\GalleryItem;
use App\Models\SiteImage;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Resolves SEO-relevant page context for an image-owning model.
 */
class PageContextResolver
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
     * @var \App\Services\AltGeneration\LocaleResolver
     */
    private $localeResolver;

    /**
     * @var array<string, array<string, mixed>>
     */
    private $pageHtmlCache = [];

    /**
     * @var float|null
     */
    private $lastPageFetchAt = null;

    /**
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Contracts\Routing\UrlGenerator $url
     * @param \App\Services\AltGeneration\LocaleResolver $localeResolver
     */
    public function __construct(ConfigRepository $config, UrlGenerator $url, LocaleResolver $localeResolver)
    {
        $this->config = $config;
        $this->url = $url;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Resolve normalized context for the given entity.
     *
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Services\AltGeneration\ImageDescriptor|null $image
     * @param string|null $locale
     * @return array<string, mixed>
     */
    public function resolve(Model $entity, ?ImageDescriptor $image = null, ?string $locale = null): array
    {
        $locale = $this->localeResolver->normalizeOrDefault($locale);
        $target = $this->targetFor($entity);

        if ($entity instanceof SiteImage) {
            return $this->resolveSiteImageContext($entity, $target, $image, $locale);
        }

        $values = $this->readContextValues($entity, $target, $locale);
        $pageTitle = $this->cleanText($this->firstMatching($values, $this->configuredContextFields($target, 'title', [
            'title',
            'name',
            'shortname',
            'meta_title',
            'position_name',
        ])));
        $description = $this->cleanText($this->firstMatching($values, $this->configuredContextFields($target, 'description', [
            'description',
            'short_desc',
            'excerpt',
            'meta_description',
            'meta_desc',
        ])));
        $body = $this->cleanBody($this->firstMatching($values, $this->configuredContextFields($target, 'body', [
            'body',
            'content',
            'text',
            'description',
            'seo',
            'item_text',
        ])));
        $category = $this->cleanText($this->firstMatching($values, $this->configuredContextFields($target, 'category', [
            'category',
            'category_name',
            'id_category',
            'genre',
            'style',
            'id_type',
            'page',
        ])));
        $purpose = $this->resolvePurpose($entity, $values, $target);
        $urlResolution = $this->resolvePageUrl($entity, $target, $image, $locale);
        $pageUrl = $urlResolution['url'];
        $currentAlt = $this->resolveCurrentAlt($entity, $target, $image, $locale);
        $currentTitle = $this->resolveCurrentTitle($entity, $target, $image, $locale);
        $surroundingText = $this->resolveSurroundingText(
            $entity,
            $target,
            $values,
            $image,
            $description,
            $pageTitle,
            $locale
        );

        return [
            'page_title' => $pageTitle,
            'page_url' => $pageUrl,
            'resolver_source' => $urlResolution['source'],
            'section_block' => $this->resolveSectionBlock($image, $purpose),
            'category' => $category,
            'purpose' => $purpose,
            'surrounding_text' => $surroundingText,
            'language' => $locale,
            'current_alt' => $currentAlt,
            'current_title' => $currentTitle,
            'sibling_image_alts' => $this->siblingImageAlts($pageUrl, $image, $locale),
            'title' => $pageTitle,
            'description' => $description,
            'body' => $body,
            'url' => $pageUrl,
        ];
    }

    /**
     * @param \App\Models\SiteImage $image
     * @param array<string, mixed> $target
     * @param \App\Services\AltGeneration\ImageDescriptor|null $descriptor
     * @param string $locale
     * @return array<string, mixed>
     */
    private function resolveSiteImageContext(
        SiteImage $image,
        array $target,
        ?ImageDescriptor $descriptor,
        string $locale
    ): array
    {
        $placement = $this->siteImagePlacement($image);

        $page = $this->cleanText($this->readAttribute($image, 'page', $locale))
            ?: $this->cleanText(isset($placement['_page']) ? (string) $placement['_page'] : null);

        $positionName = $this->cleanText($this->readAttribute($image, 'position_name', $locale));

        $block = $this->placementText($placement, 'block', $locale)
            ?: $this->humanLabel($positionName);

        $placementContextText = $this->placementText($placement, 'context_text', $locale);
        $translatedContextText = $this->placementTranslationText($placement, 'context_translation_key', $locale)
            ?: $this->placementTranslationText($placement, 'context_translation_keys', $locale);

        $pageTitle = $this->placementText($placement, 'page_title', $locale)
            ?: ($this->humanLabel($page) ?: ($locale === 'ru' ? 'Изображение сайта' : 'Site image'));

        $purpose = $this->placementText($placement, 'purpose', $locale)
            ?: ($block ?: $this->humanLabel($positionName));

        $category = $this->placementText($placement, 'category', $locale)
            ?: ($block ?: $page);

        $pageUrl = $this->resolvePlacementUrl($placement, $locale);
        $resolverSource = $pageUrl === null ? 'none' : 'site_image_placement';
        $pageUrlStatus = $pageUrl === null ? 'not_resolved' : 'resolved_from_placement';
        $visiblePageText = null;

        /*
         * SiteImage placements are manually configured in alt_generation.site_image_placements.
         * For these images the configured placement URL is the useful editorial/admin URL.
         *
         * Do NOT clear page_url only because the rendered public HTML did not expose the exact
         * original image path. Many frontend images may be lazy-loaded, transformed, served from
         * CSS/backgrounds, localized routes, CDN paths, cached markup, or resized derivatives.
         *
         * We still try to verify the image, but verification is diagnostic only for SiteImage.
         */
        if ($pageUrl !== null && $descriptor !== null && trim((string) $descriptor->path) !== '') {
            $visiblePageText = $this->surroundingTextFromPublicPage($pageUrl, $descriptor);

            if ($this->pageContainsImage($pageUrl, $descriptor)) {
                $pageUrlStatus = 'verified_on_page';
            } elseif ($this->pageFetchFailed($pageUrl)) {
                $pageUrlStatus = 'page_check_failed_but_kept';
            } else {
                $pageUrlStatus = 'not_found_on_page_but_kept';
            }

            $resolverSource .= $pageUrlStatus === 'verified_on_page'
                ? ':verified'
                : ':' . $pageUrlStatus;
        }

        $contextText = $translatedContextText ?: ($visiblePageText ?: $placementContextText);

        $currentAlt = $this->resolveCurrentAlt($image, $target, $descriptor, $locale);
        $currentTitle = $this->resolveCurrentTitle($image, $target, $descriptor, $locale);

        $surroundingText = $this->truncate(
            $this->joinContextText([
                $translatedContextText,
                $visiblePageText,
                $placementContextText,
                $purpose,
                $block,
                $this->humanLabel($positionName),
            ]),
            700
        );

        return [
            'page_title' => $pageTitle,
            'page_url' => $pageUrl,
            'page_url_status' => $pageUrlStatus,
            'resolver_source' => $resolverSource,
            'section_block' => $block,
            'category' => $category,
            'purpose' => $purpose,
            'position_name' => $positionName,
            'placement_context' => $placementContextText,
            'translated_context' => $translatedContextText,
            'visible_page_text' => $visiblePageText,
            'surrounding_text' => $surroundingText,
            'language' => $locale,
            'current_alt' => $currentAlt,
            'current_title' => $currentTitle,
            'sibling_image_alts' => $this->siblingImageAlts($pageUrl, $descriptor, $locale),
            'title' => $pageTitle,
            'description' => $contextText ?: $purpose ?: $block,
            'body' => $this->cleanBody($contextText ?: $purpose ?: $block),
            'url' => $pageUrl,
        ];
    }

    /**
     * @param \App\Models\SiteImage $image
     * @return array<string, mixed>
     */
    private function siteImagePlacement(SiteImage $image): array
    {
        $placements = (array) $this->config->get('alt_generation.site_image_placements', []);
        $page = $this->readAttribute($image, 'page');
        $position = $this->readAttribute($image, 'position_name');

        if ($page !== null && $position !== null) {
            foreach ($this->candidateSiteImagePageKeys($page) as $pageKey) {
                if (isset($placements[$pageKey][$position]) && is_array($placements[$pageKey][$position])) {
                    $placement = $placements[$pageKey][$position];
                    $placement['_page'] = $pageKey;

                    return $placement;
                }
            }
        }

        if ($position === null || trim($position) === '') {
            return [];
        }

        $matches = [];

        foreach ($placements as $placementPage => $pagePlacements) {
            if (!is_array($pagePlacements)) {
                continue;
            }

            if (isset($pagePlacements[$position]) && is_array($pagePlacements[$position])) {
                $placement = $pagePlacements[$position];
                $placement['_page'] = is_string($placementPage) ? $placementPage : null;
                $matches[] = $placement;
            }
        }

        return count($matches) === 1 ? $matches[0] : [];
    }

    /**
     * @param string $page
     * @return array<int, string>
     */
    private function candidateSiteImagePageKeys(string $page): array
    {
        $normalized = strtolower(trim($page, '/'));
        $underscore = str_replace('-', '_', $normalized);
        $aliases = [
            'main' => 'home',
            'index' => 'home',
            'gift-card' => 'gift_card',
            'gift_card_new' => 'gift_card',
            'new_stocks' => 'stocks',
            'portrait-caricature' => 'sharj',
            'caricature' => 'sharj',
        ];

        $keys = [$page, trim($page, '/'), $normalized, $underscore];

        if (isset($aliases[$normalized])) {
            $keys[] = $aliases[$normalized];
        }

        if (isset($aliases[$underscore])) {
            $keys[] = $aliases[$underscore];
        }

        return array_values(array_unique(array_filter($keys, function ($key): bool {
            return is_string($key) && trim($key) !== '';
        })));
    }

    /**
     * @param array<string, mixed> $placement
     * @return string|null
     */
    private function resolvePlacementUrl(array $placement, string $locale): ?string
    {
        $url = $placement['url'] ?? null;

        if (is_callable($url)) {
            try {
                $resolved = call_user_func($url);

                return is_string($resolved) && trim($resolved) !== ''
                    ? $this->localizePublicUrl($resolved, $locale)
                    : null;
            } catch (Throwable $exception) {
                return null;
            }
        }

        if (is_string($url)) {
            return $this->localizePublicUrl($this->resolveUrlString($url), $locale);
        }

        if (!is_array($url)) {
            return null;
        }

        $route = isset($url['route']) && is_string($url['route']) ? $url['route'] : null;
        $parameters = isset($url['parameters']) && is_array($url['parameters'])
            ? $url['parameters']
            : (isset($url['params']) && is_array($url['params']) ? $url['params'] : []);

        if ($route !== null && trim($route) !== '') {
            try {
                return $this->localizePublicUrl($this->url->route($route, $parameters), $locale);
            } catch (Throwable $exception) {
                // Fall back to the configured plain path below.
            }
        }

        $fallback = isset($url['fallback']) && is_string($url['fallback']) ? $url['fallback'] : null;

        return $fallback === null ? null : $this->localizePublicUrl($this->resolveUrlString($fallback), $locale);
    }

    /**
     * @param string $value
     * @return string|null
     */
    private function resolveUrlString(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        if (strpos($value, '/') === 0) {
            return $this->url->to($value);
        }

        try {
            return $this->url->route($value);
        } catch (Throwable $exception) {
            return null;
        }
    }

    /**
     * @param string|null $value
     * @return string|null
     */
    private function humanLabel(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $label = preg_replace('/[_-]+/', ' ', $value);
        $label = preg_replace('/\s+/', ' ', (string) $label);
        $label = trim((string) $label);

        return $label === '' ? null : ucwords($label);
    }

    /**
     * @param \App\Services\AltGeneration\ImageDescriptor|null $image
     * @param string|null $fallback
     * @return string|null
     */
    private function resolveSectionBlock(?ImageDescriptor $image, ?string $fallback): ?string
    {
        if ($image !== null && $image->field !== null) {
            return $this->humanLabel($image->field) ?: $image->field;
        }

        return $fallback;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param array<string, mixed> $target
     * @param \App\Services\AltGeneration\ImageDescriptor|null $image
     * @return string|null
     */
    private function resolveCurrentAlt(
        Model $entity,
        array $target,
        ?ImageDescriptor $image,
        string $locale
    ): ?string
    {
        if ($image === null) {
            return null;
        }

        $current = $this->cleanText($image->currentAlt);

        if ($current !== null) {
            return $current;
        }

        $field = $image->field;

        if ($field === null || $field === '') {
            return null;
        }

        $map = $this->applyMap($entity, $target, $field);
        $column = $map['alt'] ?? null;

        return $column === null ? null : $this->cleanText($this->readAttribute($entity, $column, $locale));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param array<string, mixed> $target
     * @param \App\Services\AltGeneration\ImageDescriptor|null $image
     * @return string|null
     */
    private function resolveCurrentTitle(
        Model $entity,
        array $target,
        ?ImageDescriptor $image,
        string $locale
    ): ?string
    {
        if ($image === null) {
            return null;
        }

        $current = $this->cleanText($image->currentTitle);

        if ($current !== null) {
            return $current;
        }

        $field = $image->field;

        if ($field === null || $field === '') {
            return null;
        }

        $map = $this->applyMap($entity, $target, $field);
        $column = $map['title'] ?? null;

        return $column === null ? null : $this->cleanText($this->readAttribute($entity, $column, $locale));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param array<string, mixed> $target
     * @param string $field
     * @return array{alt: string|null, title: string|null}
     */
    private function applyMap(Model $entity, array $target, string $field): array
    {
        $map = isset($target['apply_map'][$field]) && is_array($target['apply_map'][$field])
            ? $target['apply_map'][$field]
            : null;

        if ($map !== null) {
            return [
                'alt' => isset($map['alt']) && is_string($map['alt']) && $map['alt'] !== '' ? $map['alt'] : null,
                'title' => isset($map['title']) && is_string($map['title']) && $map['title'] !== '' ? $map['title'] : null,
            ];
        }

        $altColumn = $field . '_alt';

        if (method_exists($entity, 'altFieldFor')) {
            $resolved = $entity->altFieldFor($field);

            if (is_string($resolved) && $resolved !== '') {
                $altColumn = $resolved;
            }
        }

        return [
            'alt' => $altColumn,
            'title' => $field . '_title',
        ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param array<string, mixed> $target
     * @param array<string, string> $values
     * @param \App\Services\AltGeneration\ImageDescriptor|null $image
     * @param string|null $description
     * @param string|null $pageTitle
     * @return string|null
     */
    private function resolveSurroundingText(
        Model $entity,
        array $target,
        array $values,
        ?ImageDescriptor $image,
        ?string $description,
        ?string $pageTitle,
        string $locale
    ): ?string {
        if ($image !== null && $image->sourceType === 'html') {
            $htmlText = $this->surroundingTextFromHtml($entity, $image, $locale);

            if ($htmlText !== null) {
                return $htmlText;
            }
        }

        $parts = [];

        foreach (['description', 'title', 'purpose', 'category'] as $contextKey) {
            $fields = $this->configuredContextFields($target, $contextKey, []);

            foreach ($fields as $field) {
                if (isset($values[$field])) {
                    $parts[] = $this->cleanText($values[$field]);
                }
            }
        }

        if (empty(array_filter($parts))) {
            $parts[] = $description;
            $parts[] = $pageTitle;
        }

        if ($image === null && empty(array_filter($parts))) {
            foreach ($this->configuredContextFields($target, 'body', []) as $field) {
                if (isset($values[$field])) {
                    $parts[] = $this->cleanText($values[$field]);
                    break;
                }
            }
        }

        $text = implode(' ', array_values(array_unique(array_filter($parts))));

        return $this->truncate($this->cleanText($text), 500);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @return string|null
     */
    private function surroundingTextFromHtml(Model $entity, ImageDescriptor $image, string $locale): ?string
    {
        if ($image->field === null || $image->field === '') {
            return null;
        }

        $html = $this->readAttribute($entity, $image->field, $locale);

        if ($html === null || trim($html) === '') {
            return null;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $node = $this->htmlImageNode($xpath, $image);

        return $node === null ? null : $this->truncate($this->cleanText($this->nearestBlockText($node)), 500);
    }

    /**
     * @param \DOMXPath $xpath
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @return \DOMElement|null
     */
    private function htmlImageNode(DOMXPath $xpath, ImageDescriptor $image): ?DOMElement
    {
        if ($image->xpath !== null && trim($image->xpath) !== '') {
            $nodes = $xpath->query($image->xpath);

            if ($nodes !== false && $nodes->length > 0 && $nodes->item(0) instanceof DOMElement) {
                /** @var \DOMElement $node */
                $node = $nodes->item(0);

                return $node;
            }
        }

        $nodes = $xpath->query('//img');

        if ($nodes === false) {
            return null;
        }

        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            if ($this->normalizeImagePath($node->getAttribute('src')) === $image->path) {
                return $node;
            }
        }

        return null;
    }

    /**
     * @param \DOMElement $imageNode
     * @return string|null
     */
    private function nearestBlockText(DOMElement $imageNode): ?string
    {
        $node = $imageNode->parentNode;
        $depth = 0;

        while ($node instanceof DOMNode && $depth < 6) {
            if ($node instanceof DOMElement) {
                $text = $this->cleanText($node->textContent);

                if ($text !== null) {
                    return $text;
                }
            }

            $node = $node->parentNode;
            $depth++;
        }

        return null;
    }

    /**
     * @param string $path
     * @return string
     */
    private function normalizeImagePath(string $path): string
    {
        $path = trim(html_entity_decode($path, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $parts = preg_split('/[?#]/', $path, 2);
        $path = str_replace('\\', '/', $parts[0] ?? $path);

        if (preg_match('#^https?://#i', $path)) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = ltrim($path, '/');

        return strpos($path, 'storage/') === 0 ? substr($path, 8) : $path;
    }

    /**
     * @param string|null $pageUrl
     * @param \App\Services\AltGeneration\ImageDescriptor|null $image
     * @param string $locale
     * @return array<int, string>
     */
    private function siblingImageAlts(?string $pageUrl, ?ImageDescriptor $image, string $locale): array
    {
        if ($pageUrl === null || trim($pageUrl) === '') {
            return [];
        }

        try {
            $query = ImageAltSuggestion::query()
                ->where('page_url', $pageUrl)
                ->where('locale', $locale)
                ->whereIn('status', ['approved', 'applied'])
                ->orderByDesc('id')
                ->limit(50);

            if ($image !== null) {
                $query->where('image_path', '<>', $image->path);
            }

            /** @var \Illuminate\Support\Collection<int, \App\Models\ImageAltSuggestion> $suggestions */
            $suggestions = $query->get(['approved_alt', 'suggested_alt']);
        } catch (Throwable $exception) {
            return [];
        }

        $alts = [];

        foreach ($suggestions as $suggestion) {
            $alt = $this->cleanText($suggestion->approved_alt ?: $suggestion->suggested_alt);

            if ($alt !== null) {
                $alts[] = $alt;
            }
        }

        return array_values(array_unique($alts));
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
     * @param array<string, mixed> $target
     * @return array<string, string>
     */
    private function readContextValues(Model $entity, array $target, string $locale): array
    {
        $fields = array_unique(array_merge(
            $this->flattenContextFields($target),
            (array) ($target['html_fields'] ?? [])
        ));

        $values = [];

        foreach ($fields as $field) {
            if (!is_string($field) || $field === '') {
                continue;
            }

            $value = $this->readAttribute($entity, $field, $locale);

            if ($value !== null && $value !== '') {
                $values[$field] = $value;
            }
        }

        return $values;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param string $field
     * @return string|null
     */
    private function readAttribute(Model $entity, string $field, ?string $locale = null): ?string
    {
        if ($locale !== null && method_exists($entity, 'getTranslatedAttribute')) {
            try {
                $translated = $entity->getTranslatedAttribute($field, $locale, $this->localeResolver->defaultLocale());

                if ($translated !== null && trim((string) $translated) !== '') {
                    return trim((string) $translated);
                }
            } catch (Throwable $exception) {
                // Fall back to the base attribute below.
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

        return trim((string) $value);
    }

    /**
     * @param array<string, string> $values
     * @param array<int, string> $preferredFields
     * @return string|null
     */
    private function firstMatching(array $values, array $preferredFields): ?string
    {
        foreach ($preferredFields as $field) {
            if (isset($values[$field]) && trim($values[$field]) !== '') {
                return $values[$field];
            }
        }

        return null;
    }

    /**
     * @param string|null $value
     * @return string|null
     */
    private function cleanBody(?string $value): ?string
    {
        return $this->truncate($this->cleanText($value), 2000);
    }

    /**
     * @param string|null $value
     * @return string|null
     */
    private function cleanText(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = preg_replace('/\s+/u', ' ', $text);

        if ($normalized === null) {
            $normalized = preg_replace('/\s+/', ' ', $text);
        }

        $normalized = trim((string) $normalized);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @param string|null $value
     * @param int $maxLength
     * @return string|null
     */
    private function truncate(?string $value, int $maxLength): ?string
    {
        if ($value === null) {
            return null;
        }

        if (function_exists('mb_strlen') && mb_strlen($value, 'UTF-8') > $maxLength) {
            return mb_substr($value, 0, $maxLength, 'UTF-8');
        }

        if (!function_exists('mb_strlen') && strlen($value) > $maxLength) {
            return substr($value, 0, $maxLength);
        }

        return $value;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param array<string, string> $values
     * @return string|null
     */
    private function resolvePurpose(Model $entity, array $values, array $target): ?string
    {
        foreach ($this->configuredContextFields($target, 'purpose', ['purpose', 'page', 'position_name']) as $field) {
            if (isset($values[$field]) && trim($values[$field]) !== '') {
                return $this->cleanText($values[$field]);
            }
        }

        return basename(str_replace('\\', '/', get_class($entity)));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param array<string, mixed> $target
     * @param \App\Services\AltGeneration\ImageDescriptor|null $image
     * @param string $locale
     * @return array{url: string|null, source: string}
     */
    private function resolvePageUrl(Model $entity, array $target, ?ImageDescriptor $image, string $locale): array
    {
        if ($entity instanceof GalleryItem && $image !== null && $image->sourceType === 'media') {
            $url = PublicUrlResolver::galleryItemMediaSection($entity, $image->field);
            $url = $this->localizePublicUrl($url, $locale);
            $source = $url === null ? 'none' : 'gallery_item_media_section';

            return $this->verifiedPageUrlResult($url, $source, $image);
        }

        $url = $this->resolveUrl($entity, $target, $locale);
        $source = $url === null ? 'none' : $this->resolverSource($target);

        return $this->verifiedPageUrlResult($url, $source, $image);
    }

    /**
     * @param string|null $url
     * @param string $source
     * @param \App\Services\AltGeneration\ImageDescriptor|null $image
     * @return array{url: string|null, source: string}
     */
    private function verifiedPageUrlResult(?string $url, string $source, ?ImageDescriptor $image): array
    {
        if ($url === null || trim($url) === '') {
            return [
                'url' => null,
                'source' => 'none',
            ];
        }

        if ($image === null || trim((string) $image->path) === '') {
            return [
                'url' => $url,
                'source' => $source,
            ];
        }

        if (!$this->pageContainsImage($url, $image)) {
            $status = $this->pageFetchFailed($url)
                ? 'page_check_failed_but_kept'
                : 'not_found_on_page_but_kept';

            return [
                'url' => $url,
                'source' => $source . ':' . $status,
            ];
        }

        return [
            'url' => $url,
            'source' => $source . ':verified',
        ];
    }

    /**
     * @param string $url
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @return bool
     */
    private function pageContainsImage(string $url, ImageDescriptor $image): bool
    {
        $html = $this->fetchPageHtml($url);

        if ($html === null || trim($html) === '') {
            return false;
        }

        $imagePath = $this->normalizeImagePath((string) $image->path);

        if ($imagePath === '') {
            return false;
        }

        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = str_replace('\\/', '/', $html);

        $candidateUrls = $this->extractImageCandidateUrls($html);

        foreach ($candidateUrls as $candidateUrl) {
            $candidatePath = $this->normalizeImagePath($candidateUrl);

            if ($candidatePath === '') {
                continue;
            }

            if ($candidatePath === $imagePath) {
                return true;
            }

            if (strpos($candidatePath, '/storage/' . $imagePath) !== false) {
                return true;
            }

            if (strpos($candidatePath, 'storage/' . $imagePath) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract only real frontend image URLs from image-related HTML attributes.
     *
     * @param string $html
     * @return array<int, string>
     */
    private function extractImageCandidateUrls(string $html): array
    {
        $urls = [];

        if (preg_match_all('/<(img|source|link)\b[^>]*>/i', $html, $tagMatches)) {
            foreach ($tagMatches[0] as $tag) {
                if (preg_match_all('/\s(src|data-src|data-lazy-src|data-original|href)=["\']([^"\']+)["\']/i', $tag, $attrMatches)) {
                    foreach ($attrMatches[2] as $value) {
                        $urls[] = trim($value);
                    }
                }

                if (preg_match_all('/\s(srcset|data-srcset)=["\']([^"\']+)["\']/i', $tag, $srcsetMatches)) {
                    foreach ($srcsetMatches[2] as $srcset) {
                        foreach (explode(',', $srcset) as $srcsetPart) {
                            $srcsetPart = trim($srcsetPart);

                            if ($srcsetPart === '') {
                                continue;
                            }

                            $urls[] = trim(explode(' ', $srcsetPart)[0]);
                        }
                    }
                }
            }
        }

        if (preg_match_all('/\s(data-bg|data-background|data-bg-src)=["\']([^"\']+)["\']/i', $html, $bgMatches)) {
            foreach ($bgMatches[2] as $value) {
                $urls[] = trim($value);
            }
        }

        if (preg_match_all('/background-image\s*:\s*url\((["\']?)(.*?)\1\)/i', $html, $styleMatches)) {
            foreach ($styleMatches[2] as $value) {
                $urls[] = trim($value);
            }
        }

        return array_values(array_unique(array_filter($urls)));
    }

    /**
     * @param string $url
     * @return string|null
     */
    private function fetchPageHtml(string $url): ?string
    {
        $result = $this->fetchPageResult($url);

        return isset($result['html']) && is_string($result['html']) ? $result['html'] : null;
    }

    /**
     * @param string $url
     * @return bool
     */
    private function pageFetchFailed(string $url): bool
    {
        $result = $this->fetchPageResult($url);

        return isset($result['failed']) && $result['failed'] === true;
    }

    /**
     * @param string $url
     * @return array<string, mixed>
     */
    private function fetchPageResult(string $url): array
    {
        $cacheKey = trim($url);

        if (array_key_exists($cacheKey, $this->pageHtmlCache)) {
            return $this->pageHtmlCache[$cacheKey];
        }

        $retries = $this->pageFetchRetries();
        $result = [
            'html' => null,
            'failed' => true,
            'status_code' => null,
            'retryable' => false,
        ];

        for ($attempt = 0; $attempt <= $retries; $attempt++) {
            $this->throttlePageFetch();

            $result = $this->requestPageHtml($url);

            if (!isset($result['retryable']) || $result['retryable'] !== true || $attempt >= $retries) {
                break;
            }

            $this->sleepMilliseconds($this->pageFetchDelayMs() * ($attempt + 1));
        }

        $this->pageHtmlCache[$cacheKey] = $result;

        return $this->pageHtmlCache[$cacheKey];
    }

    /**
     * @param string $url
     * @return array<string, mixed>
     */
    private function requestPageHtml(string $url): array
    {
        $http_response_header = [];
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $this->pageFetchTimeoutSeconds(),
                'ignore_errors' => true,
                'header' => "User-Agent: ViarCanvasAltVerifier/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        try {
            $html = @file_get_contents($url, false, $context);
        } catch (Throwable $exception) {
            $html = false;
        }

        $statusCode = $this->httpStatusCode(isset($http_response_header) ? $http_response_header : []);
        $retryable = $html === false || $this->isRetryableStatusCode($statusCode);
        $failed = $html === false
            || (is_string($html) && trim($html) === '')
            || ($statusCode !== null && $statusCode >= 400);

        return [
            'html' => !$failed && is_string($html) ? $html : null,
            'failed' => $failed,
            'status_code' => $statusCode,
            'retryable' => $retryable,
        ];
    }

    /**
     * @return void
     */
    private function throttlePageFetch(): void
    {
        $delayMs = $this->pageFetchDelayMs();

        if ($delayMs <= 0) {
            $this->lastPageFetchAt = microtime(true);

            return;
        }

        if ($this->lastPageFetchAt !== null) {
            $elapsedMs = (int) floor((microtime(true) - $this->lastPageFetchAt) * 1000);
            $remainingMs = $delayMs - $elapsedMs;

            if ($remainingMs > 0) {
                $this->sleepMilliseconds($remainingMs);
            }
        }

        $this->lastPageFetchAt = microtime(true);
    }

    /**
     * @return int
     */
    private function pageFetchDelayMs(): int
    {
        return max(0, (int) $this->config->get('alt_generation.page_fetch_delay_ms', 500));
    }

    /**
     * @return int
     */
    private function pageFetchTimeoutSeconds(): int
    {
        return max(1, (int) $this->config->get('alt_generation.page_fetch_timeout_seconds', 10));
    }

    /**
     * @return int
     */
    private function pageFetchRetries(): int
    {
        return max(0, (int) $this->config->get('alt_generation.page_fetch_retries', 2));
    }

    /**
     * @param array<int, string> $headers
     * @return int|null
     */
    private function httpStatusCode(array $headers): ?int
    {
        $statusCode = null;

        foreach ($headers as $header) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#i', (string) $header, $matches) === 1) {
                $statusCode = (int) $matches[1];
            }
        }

        return $statusCode;
    }

    /**
     * @param int|null $statusCode
     * @return bool
     */
    private function isRetryableStatusCode(?int $statusCode): bool
    {
        return $statusCode !== null && in_array($statusCode, [429, 500, 502, 503, 504], true);
    }

    /**
     * @param int $milliseconds
     * @return void
     */
    private function sleepMilliseconds(int $milliseconds): void
    {
        if ($milliseconds > 0) {
            usleep($milliseconds * 1000);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param array<string, mixed> $target
     * @return string|null
     */
    private function resolveUrl(Model $entity, array $target, string $locale): ?string
    {
        if (array_key_exists('url_resolver', $target) && $target['url_resolver'] === null) {
            return null;
        }

        $resolver = $target['url_resolver'] ?? null;

        if (is_callable($resolver)) {
            return $this->localizePublicUrl($this->resolveCallableUrl($entity, $resolver), $locale);
        }

        if (is_string($resolver) && $resolver !== '') {
            try {
                return $this->localizePublicUrl($this->url->route($resolver, $this->routeParameters($entity, $locale)), $locale);
            } catch (Throwable $exception) {
                return null;
            }
        }

        return $this->localizePublicUrl($this->fallbackUrlFromEntity($entity, $locale), $locale);
    }

    /**
     * @param array<string, mixed> $target
     * @return string
     */
    private function resolverSource(array $target): string
    {
        $resolver = $target['url_resolver'] ?? null;

        if (is_array($resolver)) {
            return implode('@', array_map(static function ($part): string {
                return is_string($part) ? class_basename($part) : (string) $part;
            }, $resolver));
        }

        if (is_string($resolver) && $resolver !== '') {
            return 'route:' . $resolver;
        }

        if (is_callable($resolver)) {
            return 'callable';
        }

        return 'fallback_entity_field';
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param callable $resolver
     * @return string|null
     */
    private function resolveCallableUrl(Model $entity, callable $resolver): ?string
    {
        try {
            $url = call_user_func($resolver, $entity);

            if (is_string($url) && trim($url) !== '') {
                return $url;
            }

            if ($url === null) {
                return null;
            }
        } catch (Throwable $exception) {
            // Some legacy helpers accept an id instead of the model.
        }

        try {
            $url = call_user_func($resolver, $entity->getKey());

            return is_string($url) && trim($url) !== '' ? $url : null;
        } catch (Throwable $exception) {
            return null;
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @return array<string, string>
     */
    private function routeParameters(Model $entity, string $locale): array
    {
        $slug = $this->readAttribute($entity, 'slug', $locale)
            ?: $this->readAttribute($entity, 'url', $locale)
                ?: $this->readAttribute($entity, 'page', $locale);

        if ($slug === null || trim($slug) === '') {
            return [];
        }

        $slug = trim($slug, '/');

        return [
            'slug' => $slug,
            'page' => $slug,
        ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @return string|null
     */
    private function fallbackUrlFromEntity(Model $entity, string $locale): ?string
    {
        foreach (['page_url', 'url', 'slug', 'meta_url'] as $field) {
            $value = $this->readAttribute($entity, $field, $locale);

            if ($value === null || trim($value) === '') {
                continue;
            }

            if (preg_match('#^https?://#i', $value)) {
                return $value;
            }

            return $this->url->to('/' . trim($value, '/'));
        }

        return null;
    }

    /**
     * @param string|null $url
     * @param string $locale
     * @return string|null
     */
    private function localizePublicUrl(?string $url, string $locale): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);
        $parts = parse_url($url);
        $path = isset($parts['path']) ? (string) $parts['path'] : '/';
        $path = '/' . ltrim($path, '/');
        $pathWithoutLocale = $this->stripLocalePrefix($path);

        if ($this->isExcludedPublicPath($pathWithoutLocale)) {
            return null;
        }

        $appHost = parse_url((string) $this->config->get('app.url', ''), PHP_URL_HOST);
        $urlHost = isset($parts['host']) ? (string) $parts['host'] : null;

        if ($urlHost !== null && $appHost !== null && strcasecmp($urlHost, (string) $appHost) !== 0) {
            return $url;
        }

        $defaultLocale = $this->localeResolver->defaultLocale();
        $hideDefault = (bool) $this->config->get('laravellocalization.hideDefaultLocaleInURL', false);
        $needsPrefix = !($locale === $defaultLocale && $hideDefault);
        $localizedPath = ($needsPrefix ? '/' . $locale : '') . ($pathWithoutLocale === '/' ? '' : $pathWithoutLocale);
        $localizedPath = $localizedPath === '' ? '/' : $localizedPath;

        if ($urlHost === null) {
            return $this->url->to($localizedPath);
        }

        $scheme = isset($parts['scheme']) ? (string) $parts['scheme'] : 'https';
        $port = isset($parts['port']) ? ':' . (string) $parts['port'] : '';
        $query = isset($parts['query']) ? '?' . (string) $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . (string) $parts['fragment'] : '';

        return $scheme . '://' . $urlHost . $port . $localizedPath . $query . $fragment;
    }

    /**
     * @param string $path
     * @return string
     */
    private function stripLocalePrefix(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $segments = explode('/', trim($path, '/'));
        $first = $segments[0] ?? '';

        if ($first !== '' && in_array($first, $this->localeResolver->supportedLocales(), true)) {
            array_shift($segments);

            return empty($segments) ? '/' : '/' . implode('/', $segments);
        }

        return $path;
    }

    /**
     * @param string $path
     * @return bool
     */
    private function isExcludedPublicPath(string $path): bool
    {
        $clean = strtolower(ltrim(str_replace('\\', '/', $path), '/'));

        foreach ((array) $this->config->get('alt_generation.exclude.path_prefixes', []) as $prefix) {
            if (!is_string($prefix) || trim($prefix) === '') {
                continue;
            }

            $prefix = strtolower(ltrim(str_replace('\\', '/', trim($prefix)), '/'));

            if (strpos($clean, $prefix) === 0) {
                return true;
            }
        }

        foreach ((array) $this->config->get('alt_generation.exclude.exclude_patterns', []) as $pattern) {
            if (is_string($pattern) && trim($pattern) !== '' && @preg_match($pattern, $clean) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @return string|null
     */
    private function resolveLanguage(Model $entity, array $target): ?string
    {
        $language = $target['language'] ?? null;

        if (is_callable($language)) {
            try {
                $resolved = call_user_func($language, $entity);

                if (is_string($resolved) && trim($resolved) !== '') {
                    return $resolved;
                }
            } catch (Throwable $exception) {
                // Fall back to fields below.
            }
        }

        if (is_string($language) && trim($language) !== '') {
            if ($language === '@app_locale') {
                return $this->localeResolver->currentLocale();
            }

            $value = $this->readAttribute($entity, $language, $this->localeResolver->currentLocale());

            if ($value !== null && trim($value) !== '') {
                return $this->localeResolver->normalizeOrDefault($value);
            }

            return $this->localeResolver->isSupported($language)
                ? $language
                : $this->localeResolver->defaultLocale();
        }

        foreach (['language', 'locale', 'lang'] as $field) {
            $value = $this->readAttribute($entity, $field, $this->localeResolver->currentLocale());

            if ($value !== null && trim($value) !== '') {
                return $this->localeResolver->normalizeOrDefault($value);
            }
        }

        return $this->localeResolver->defaultLocale();
    }

    /**
     * @param array<string, mixed> $target
     * @param string $key
     * @param array<int, string> $defaults
     * @return array<int, string>
     */
    private function configuredContextFields(array $target, string $key, array $defaults): array
    {
        $context = $target['context'] ?? [];

        if (is_array($context) && array_key_exists($key, $context)) {
            return array_values(array_filter((array) $context[$key], 'is_string'));
        }

        return $defaults;
    }

    /**
     * @param array<string, mixed> $target
     * @return array<int, string>
     */
    private function flattenContextFields(array $target): array
    {
        $context = $target['context'] ?? [];

        if (!is_array($context)) {
            return [];
        }

        $fields = [];

        foreach ($context as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $fields[] = $value;
                continue;
            }

            foreach ((array) $value as $field) {
                if (is_string($field)) {
                    $fields[] = $field;
                }
            }
        }

        return $fields;
    }


    /**
     * @param array<string, mixed> $placement
     * @param string $key
     * @param string $locale
     * @return string|null
     */
    private function placementTranslationText(array $placement, string $key, string $locale): ?string
    {
        if (!array_key_exists($key, $placement)) {
            return null;
        }

        $value = $placement[$key];
        $keys = [];

        if (is_string($value)) {
            $keys[] = $value;
        } elseif (is_array($value)) {
            $localeKeys = array_values(array_unique(array_filter([
                $locale,
                $this->localeResolver->defaultLocale(),
                'ru',
                'en',
                'de',
                'lv',
                'lt',
                'pl',
                'ee',
            ])));

            foreach ($localeKeys as $localeKey) {
                if (isset($value[$localeKey])) {
                    foreach ((array) $value[$localeKey] as $translationKey) {
                        if (is_string($translationKey) && trim($translationKey) !== '') {
                            $keys[] = $translationKey;
                        }
                    }
                }
            }

            foreach ($value as $translationKey) {
                if (is_string($translationKey) && trim($translationKey) !== '') {
                    $keys[] = $translationKey;
                }
            }
        }

        foreach (array_values(array_unique($keys)) as $translationKey) {
            $text = $this->translationText($translationKey, $locale);

            if ($text !== null) {
                return $text;
            }
        }

        return null;
    }

    /**
     * @param string $translationKey
     * @param string $locale
     * @return string|null
     */
    private function translationText(string $translationKey, string $locale): ?string
    {
        $translationKey = trim($translationKey);

        if ($translationKey === '') {
            return null;
        }

        try {
            $translated = trans($translationKey, [], $locale);
        } catch (Throwable $exception) {
            return null;
        }

        if (!is_string($translated) || trim($translated) === '' || $translated === $translationKey) {
            return null;
        }

        return $this->cleanText($translated);
    }

    /**
     * @param string $pageUrl
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @return string|null
     */
    private function surroundingTextFromPublicPage(string $pageUrl, ImageDescriptor $image): ?string
    {
        $html = $this->fetchPageHtml($pageUrl);

        if ($html === null || trim($html) === '') {
            return null;
        }

        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = str_replace('\\/', '/', $html);

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $node = $this->publicPageImageNode($xpath, $image);

        if ($node === null) {
            return null;
        }

        return $this->nearestMeaningfulBlockText($node);
    }

    /**
     * @param \DOMXPath $xpath
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @return \DOMElement|null
     */
    private function publicPageImageNode(DOMXPath $xpath, ImageDescriptor $image): ?DOMElement
    {
        $nodes = $xpath->query('//*');

        if ($nodes === false) {
            return null;
        }

        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            foreach ($this->imageUrlsFromElement($node) as $candidateUrl) {
                if ($this->imageUrlMatchesDescriptor($candidateUrl, $image)) {
                    return $node;
                }
            }
        }

        return null;
    }

    /**
     * @param \DOMElement $node
     * @return array<int, string>
     */
    private function imageUrlsFromElement(DOMElement $node): array
    {
        $urls = [];
        $attributes = [
            'src',
            'data-src',
            'data-lazy-src',
            'data-original',
            'data-bg',
            'data-background',
            'data-bg-src',
            'href',
        ];

        foreach ($attributes as $attribute) {
            if ($node->hasAttribute($attribute)) {
                $urls[] = trim($node->getAttribute($attribute));
            }
        }

        foreach (['srcset', 'data-srcset'] as $attribute) {
            if (!$node->hasAttribute($attribute)) {
                continue;
            }

            foreach (explode(',', $node->getAttribute($attribute)) as $srcsetPart) {
                $srcsetPart = trim($srcsetPart);

                if ($srcsetPart === '') {
                    continue;
                }

                $urls[] = trim(explode(' ', $srcsetPart)[0]);
            }
        }

        if ($node->hasAttribute('style')) {
            $style = $node->getAttribute('style');

            if (preg_match_all('/background-image\s*:\s*url\((["\']?)(.*?)\1\)/i', $style, $styleMatches)) {
                foreach ($styleMatches[2] as $value) {
                    $urls[] = trim($value);
                }
            }
        }

        return array_values(array_unique(array_filter($urls)));
    }

    /**
     * @param string $candidateUrl
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @return bool
     */
    private function imageUrlMatchesDescriptor(string $candidateUrl, ImageDescriptor $image): bool
    {
        $candidatePath = $this->normalizeImagePath($candidateUrl);
        $imagePath = $this->normalizeImagePath((string) $image->path);

        if ($candidatePath === '' || $imagePath === '') {
            return false;
        }

        if ($candidatePath === $imagePath) {
            return true;
        }

        if (strpos($candidatePath, $imagePath) !== false || strpos($imagePath, $candidatePath) !== false) {
            return true;
        }

        if (basename($candidatePath) === basename($imagePath)) {
            return true;
        }

        return $this->pathWithoutExtension($candidatePath) === $this->pathWithoutExtension($imagePath);
    }

    /**
     * @param string $path
     * @return string
     */
    private function pathWithoutExtension(string $path): string
    {
        $directory = trim((string) dirname($path), '.');
        $filename = pathinfo($path, PATHINFO_FILENAME);

        return trim($directory . '/' . $filename, '/');
    }

    /**
     * @param \DOMElement $imageNode
     * @return string|null
     */
    private function nearestMeaningfulBlockText(DOMElement $imageNode): ?string
    {
        $node = $imageNode;
        $depth = 0;

        while ($node instanceof DOMNode && $depth < 9) {
            if ($node instanceof DOMElement) {
                $tagName = strtolower($node->tagName);

                if (!in_array($tagName, ['img', 'source', 'picture', 'svg', 'path'], true)) {
                    $text = $this->cleanText($node->textContent);

                    if ($this->isMeaningfulContextText($text)) {
                        return $this->truncate($text, 700);
                    }
                }
            }

            $node = $node->parentNode;
            $depth++;
        }

        return null;
    }

    /**
     * @param string|null $text
     * @return bool
     */
    private function isMeaningfulContextText(?string $text): bool
    {
        if ($text === null) {
            return false;
        }

        $text = trim($text);

        if ($text === '') {
            return false;
        }

        if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') < 20) {
            return false;
        }

        if (!function_exists('mb_strlen') && strlen($text) < 20) {
            return false;
        }

        return preg_match('/[A-Za-zА-Яа-яЁёÄÖÜäöüÕõŠšŽžĀ-ž]/u', $text) === 1;
    }

    /**
     * @param array<int, mixed> $parts
     * @return string|null
     */
    private function joinContextText(array $parts): ?string
    {
        $result = [];
        $seen = [];

        foreach ($parts as $part) {
            $text = $this->cleanText(is_string($part) ? $part : null);

            if ($text === null) {
                continue;
            }

            $key = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $result[] = $text;
        }

        if (empty($result)) {
            return null;
        }

        return implode(' ', $result);
    }

    private function placementText(array $placement, string $key, string $locale): ?string
    {
        if (!array_key_exists($key, $placement)) {
            return null;
        }

        $value = $placement[$key];

        if (is_array($value)) {
            $localeKeys = array_values(array_unique(array_filter([
                $locale,
                $this->localeResolver->defaultLocale(),
                'ru',
                'en',
                'de',
                'lv',
                'lt',
                'pl',
                'ee',
            ])));

            foreach ($localeKeys as $localeKey) {
                if (isset($value[$localeKey]) && is_string($value[$localeKey]) && trim($value[$localeKey]) !== '') {
                    return $this->cleanText($value[$localeKey]);
                }
            }

            return null;
        }

        if ($value === null || is_object($value)) {
            return null;
        }

        return $this->cleanText((string) $value);
    }
}
