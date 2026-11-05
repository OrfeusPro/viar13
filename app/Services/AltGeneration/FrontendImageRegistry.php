<?php

namespace App\Services\AltGeneration;

use App\Models\FrontendImage;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/** Explicit template inventory; never evaluates Blade or executes HTTP routes. */
class FrontendImageRegistry
{
    private $bindings = [];
    private $bindingSizes = [];
    private $bindingBytes = 0;
    private $applied = [];
    private $persist = false;
    private $sourceCache = [];
    private ?bool $frontendTableExists = null;
    private ?bool $suggestionsTableExists = null;

    public function inlinePath(string $data): ?string
    {
        if (!preg_match('#^data:image/(png|jpeg|webp|gif);base64,([A-Za-z0-9+/=\r\n]+)$#D', $data, $match) || strlen($data) > 12000000) {
            return null;
        }
        return '/storage/frontend-alt-inline/' . hash('sha256', $data) . '.' . ($match[1] === 'jpeg' ? 'jpg' : $match[1]);
    }

    public function inlineTemplates(string $file): array
    {
        $absolute = public_path($file);
        $key = 'frontend-alt-inline:' . hash('sha256', $file . ':' . filemtime($absolute));
        return cache()->remember($key, 3600, function () use ($absolute) {
            $paths = [];
            foreach ($this->inlineTemplateData($absolute) as $data) {
                $path = $this->inlinePath($data);
                if (!$path) {
                    throw new RuntimeException('Unsupported inline template icon.');
                }
                $paths[] = $path;
            }
            return $paths;
        });
    }

    /** Read one JSON object at a time: the constructor scripts exceed 20 MB. */
    private function inlineTemplateData(string $absolute): iterable
    {
        $handle = fopen($absolute, 'rb');
        if (!$handle) {
            throw new RuntimeException('Cannot read template icons: ' . $absolute);
        }
        $depth = 0;
        $quoted = false;
        $escaped = false;
        $record = '';
        try {
            while (!feof($handle)) {
                $chunk = fread($handle, 65536);
                for ($i = 0, $length = strlen($chunk); $i < $length; $i++) {
                    $character = $chunk[$i];
                    if ($depth === 0 && $character !== '{') {
                        continue;
                    }
                    $record .= $character;
                    if (strlen($record) > 12000000) {
                        throw new RuntimeException('Template icon exceeds the size limit.');
                    }
                    if ($quoted) {
                        if ($escaped) {
                            $escaped = false;
                        } elseif ($character === '\\') {
                            $escaped = true;
                        } elseif ($character === '"') {
                            $quoted = false;
                        }
                        continue;
                    }
                    if ($character === '"') {
                        $quoted = true;
                    } elseif ($character === '{') {
                        $depth++;
                    } elseif ($character === '}' && --$depth === 0) {
                        $template = json_decode($record, true);
                        if (!is_array($template) || !isset($template['icon'])) {
                            throw new RuntimeException('Invalid template icon JSON: ' . $absolute);
                        }
                        $record = '';
                        yield $template['icon'];
                    }
                }
            }
            if ($depth !== 0) {
                throw new RuntimeException('Incomplete template icon JSON: ' . $absolute);
            }
        } finally {
            fclose($handle);
        }
    }

    public function inlineAttributes(string $placement, string $file): array
    {
        return array_map(function ($path) use ($placement) {
            return $this->attributesFor($placement, $path, '');
        }, $this->inlineTemplates($file));
    }

    public function attributesForList(string $placement, string $csv): array
    {
        $attributes = [];
        foreach (array_filter(explode(',', $csv)) as $path) {
            $attributes[$path] = $this->attributesFor($placement, order_image_url($path), '');
        }
        return $attributes;
    }

    public function suggestionPath(string $path): string
    {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        $path = ltrim($path, '/');
        return strpos($path, 'storage/') === 0 ? substr($path, 8) : $path;
    }

    public function descriptor(FrontendImage $entity, string $locale): ?ImageDescriptor
    {
        $context = $entity->context['locales'][$locale] ?? null;
        if ($context === null || (new FrontendLegacySources())->fromContext($context, $entity->image_path)) {
            return null;
        }
        $path = $entity->image_path;
        $absolute = preg_match('#^https?://#i', $path) ? null : public_path(rawurldecode(ltrim($path, '/')));
        if (strpos($path, '/storage/') === 0 && !is_file($absolute)) {
            $absolute = storage_path('app/public/' . rawurldecode(substr($path, 9)));
        }
        return new ImageDescriptor($this->suggestionPath($path), 'image_path',
            $context['current_alt'] ?? null, $context['current_title'] ?? null, 'frontend', null,
            $absolute, preg_match('#^https?://#i', $path) ? $path : url($path), $context);
    }

    public function normalize(?string $path): ?string
    {
        $path = trim(html_entity_decode((string) $path, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($path === '' || preg_match('#^(?:data:|blob:|javascript:|\#)#i', $path) || strpos($path, '{{') !== false || strpos($path, '${') !== false) {
            return null;
        }
        if (!mb_check_encoding($path, 'UTF-8')) {
            $path = preg_replace_callback('/[\x80-\xFF]/', function ($match) {
                return rawurlencode($match[0]);
            }, $path);
        }
        $host = parse_url($path, PHP_URL_HOST);
        if ($host) {
            $hosts = array_merge([
                (string) parse_url(config('app.url'), PHP_URL_HOST),
                (string) parse_url(url('/'), PHP_URL_HOST),
            ], (array) config('frontend_alt.hosts', []));
            if (!in_array(strtolower($host), array_map('strtolower', $hosts), true)) {
                return preg_match('#^https?://#i', $path) ? $path : null;
            }
            // parse_url(PATH) can replace UTF-8 continuation bytes under Windows locales.
            // Remove the already verified authority without modifying filename bytes.
            $path = preg_replace('#^(?:https?:)?//[^/]*(?=/|$)#i', '', $path);
        }
        $path = preg_split('/[?#]/', $path, 2)[0];
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if (preg_match('#(^|/)\.\.(/|$)#', $path)) {
            return null;
        }
        return $path !== '' ? '/' . $path : null;
    }

    public function attributesFor(string $placement, ?string $path, ?string $alt = null, ?string $title = null): string
    {
        $path = $this->normalize($path);
        $locale = app(LocaleResolver::class)->currentLocale();
        // Registry rows for existing model images are aliases, never new generations.
        if ($path !== null && ($this->frontendTableExists ??= Schema::hasTable('frontend_images'))) {
            $id = FrontendImage::identity($placement, $path);
            if (!array_key_exists($id, $this->sourceCache)) {
                try {
                    if (count($this->sourceCache) >= 256) {
                        $this->sourceCache = [];
                    }
                    $this->sourceCache[$id] = FrontendImage::find($id);
                } catch (\Throwable $exception) {
                    $this->sourceCache[$id] = null;
                }
            }
            $entity = $this->sourceCache[$id];
            $context = $entity ? ($entity->context['placements'][$placement]['locales'][$locale]
                ?? $entity->context['locales'][$locale] ?? []) : [];
            $source = (new FrontendLegacySources())->fromContext($context, $path);
            if ($source) {
                return app(AltAttributeResolver::class)->attributesFor(
                    $source, $source['field'], $source['path'], $locale, $alt, $title
                );
            }
        }
        $cacheKey = ($path === null ? '' : FrontendImage::identity('', $path)) . ':' . $locale;
        if ($path !== null && ($this->suggestionsTableExists ??= Schema::hasTable('image_alt_suggestions'))
            && !array_key_exists($cacheKey, $this->applied)) {
            if (count($this->applied) >= 256) {
                $this->applied = [];
            }
            $this->applied[$cacheKey] = null;
            try {
                $this->applied[$cacheKey] = \App\Models\ImageAltSuggestion::query()
                    ->where('imageable_id', FrontendImage::identity('', $path))
                    ->where('image_path', $this->suggestionPath($path))
                    ->where('imageable_type', FrontendImage::class)->where('field', 'image_path')
                    ->where('status', 'applied')->where('locale', $locale)
                    ->orderByDesc('applied_at')->orderByDesc('id')->first();
            } catch (\Throwable $exception) {
                // Deploying templates before the migration must preserve existing attributes.
                report($exception);
            }
        }
        $row = $this->applied[$cacheKey] ?? null;
        if ($row) {
            $alt = $row->approved_alt !== null ? $row->approved_alt : ($row->suggested_alt ?? $alt);
            $title = $row->approved_title !== null ? $row->approved_title : ($row->suggested_title ?? $title);
        }
        return 'alt="' . e(strip_tags((string) $alt)) . '"' . ($title !== null && $title !== '' ? ' title="' . e(strip_tags($title)) . '"' : '');
    }

    private function excludedFromScan(?string $path): bool
    {
        $path = $this->normalize($path);
        if ($path === null) {
            return false;
        }
        // Apply directory rules to both local paths and absolute image URLs.
        $path = preg_replace('#^https?://[^/]*(?=/|$)#i', '', $path);
        $path = ltrim(str_replace('\\', '/', rawurldecode(preg_split('/[?#]/', $path, 2)[0])), '/');
        $path = preg_replace('#^(?:public/)?storage/#', '', $path);
        foreach ((array) config('frontend_alt.excluded_path_prefixes', ['orders/', 'uploads/']) as $prefix) {
            $prefix = trim((string) $prefix, '/');
            if ($prefix !== '' && ($path === $prefix || strpos($path, $prefix . '/') === 0)) {
                return true;
            }
        }
        return false;
    }

    /** @return iterable<FrontendImage> */
    public function entities(array $locales, ?string $since, bool $persist): iterable
    {
        $this->persist = $persist;
        $this->bindings = [];
        $this->bindingSizes = [];
        $this->bindingBytes = 0;
        $this->sourceCache = [];
        $this->applied = [];
        if ($persist && !Schema::hasTable('frontend_images')) {
            throw new RuntimeException('Run migrations before scanning FrontendImage sources (frontend_images table is missing).');
        }
        $originalLocale = app()->getLocale();
        $legacy = new FrontendLegacySources();
        $legacy->indexExisting($this);
        $inventory = new FrontendImageInventory();
        try {
            // The catalog is large; loading it with every Laravel boot exhausts
            // the default PHP memory limit even when no ALT scan is requested.
            foreach ((array) require resource_path('frontend_alt_catalog.php') as $view => $definition) {
                $rows = [];
                foreach ($locales as $locale) {
                    app()->setLocale($locale);
                    foreach ($definition['images'] as $image) {
                        $paths = isset($image['binding'])
                            ? $this->bindingPaths($image['binding'], $locale)
                            : (is_callable($image['path'] ?? null) ? call_user_func($image['path']) : ($image['path'] ?? []));
                        foreach ((array) $paths as $candidate) {
                            $path = $this->normalize(is_array($candidate) ? ($candidate['path'] ?? null) : $candidate);
                            if ($path === null || $this->excludedFromScan($path)
                                || (is_array($candidate) && $this->excludedFromScan($candidate['owner_path'] ?? null))) {
                                continue;
                            }
                            $id = FrontendImage::identity($view, $path);
                            if (!isset($rows[$id])) {
                                $rows[$id] = ['id' => $id, 'placement' => $view, 'image_path' => $path, 'page_url' => null, 'context' => ['locales' => []]];
                            }
                            $context = [
                                'view' => $view,
                                'line' => $image['line'],
                                'page_url' => $this->placementUrl($view, $locale, $definition['route'] ?? null),
                                'page_title' => $definition['title'] ?? null,
                                'section_block' => $view,
                                'current_alt' => $image['alt'] ?? null,
                                'current_title' => $image['title'] ?? null,
                                'translated_context' => $this->translatedContext($image['translations'] ?? [], $locale),
                            ];
                            if (is_array($candidate)) {
                                $context = array_merge($context, \Illuminate\Support\Arr::except($candidate, ['path']));
                            }
                            $source = $legacy->resolve($context, $path, $this);
                            if ($source) {
                                $context['legacy_source'] = $source;
                            }
                            $rows[$id]['context']['locales'][$locale] = $context;
                            $rows[$id]['page_url'] = $context['page_url'];
                        }
                    }
                }
                foreach ($rows as $row) {
                    $row['context'] = $this->mergeContexts([], $row['context']);
                    $inventory->put($row, $this);
                }
            }
            // Only emit a file once, after all template/locale contexts have been merged.
            foreach ($inventory->chunks() as $chunk) {
                $existingRows = $persist ? FrontendImage::whereIn('id', array_keys($chunk))->get()->keyBy('id') : collect();
                $entities = [];
                $inserts = [];
                foreach ($chunk as $row) {
                    $existing = $existingRows->get($row['id']);
                    if ($existing && $existing->image_path !== $row['image_path']) {
                        throw new RuntimeException('Frontend image identity collision: ' . $row['image_path']);
                    }
                    $entity = $existing ?: new FrontendImage();
                    if ($existing) {
                        $row['context'] = $this->mergeContexts($existing->context ?? [], $row['context']);
                    }
                    $row['page_url'] = $this->contextPageUrl($row['context']);
                    if ($existing) {
                        // MySQL JSON reorders object keys; do not rewrite an unchanged context.
                        if ($existing->context == $row['context']) {
                            unset($row['context']);
                        }
                    }
                    $entity->fill($row);
                    if ($persist && !$existing) {
                        $entity->setCreatedAt($entity->freshTimestamp());
                        $entity->setUpdatedAt($entity->created_at);
                        $inserts[] = $entity->getAttributes();
                        $entity->exists = true;
                    } elseif ($persist && $entity->isDirty()) {
                        $entity->save();
                    }
                    $entities[] = $entity;
                }
                if ($inserts) {
                    FrontendImage::insert($inserts);
                }
                foreach ($entities as $entity) {
                    // New/changed inventory is always scanned; --since includes existing recent rows.
                    if ($since === null || !$entity->updated_at || $entity->updated_at->gte($since)) {
                        yield $entity;
                    }
                }
            }
        } finally {
            $inventory->close();
            app()->setLocale($originalLocale);
        }
    }

    /** Keep usages for lookup; choose a stable, useful representative per language. */
    public function mergeContexts(array $left, array $right): array
    {
        $placements = [];
        foreach ([$left, $right] as $context) {
            $usages = $context['placements'] ?? [];
            if (!$usages) {
                foreach ($context['locales'] ?? [] as $locale => $localized) {
                    $usages[$localized['view'] ?? '']['locales'][$locale] = $localized;
                }
            }
            foreach ($usages as $view => $usage) {
                foreach ($usage['locales'] ?? [] as $locale => $localized) {
                    $placements[$view]['locales'][$locale] = $localized;
                }
            }
        }
        ksort($placements);
        $locales = [];
        $ranks = [];
        foreach ($placements as $view => $usage) {
            foreach ($usage['locales'] as $locale => $localized) {
                $rank = [isset($localized['legacy_source']) ? 1 : 0, empty($localized['page_url']) ? 1 : 0,
                    $view, $localized['line'] ?? 0];
                if (!isset($ranks[$locale]) || $rank < $ranks[$locale]) {
                    $locales[$locale] = $localized;
                    $ranks[$locale] = $rank;
                }
            }
        }
        return ['locales' => $locales, 'placements' => $placements];
    }

    public function contextPageUrl(array $context): ?string
    {
        foreach ($context['locales'] ?? [] as $localized) {
            if (!empty($localized['page_url'])) {
                return $localized['page_url'];
            }
        }
        return null;
    }

    private function translatedContext(array $keys, string $locale): string
    {
        $parts = [];
        foreach ($keys as $key) {
            $value = trans($key, [], $locale);
            if (is_string($value) && $value !== $key) {
                $parts[] = trim(strip_tags($value));
            }
        }
        return mb_substr(implode(' ', array_unique($parts)), 0, 1800);
    }

    private function placementUrl(string $view, string $locale, ?string $route): ?string
    {
        foreach (config('frontend_alt.page_routes', []) as $prefix => $name) {
            if ($route === null && strpos($view, $prefix) === 0) {
                $route = $name;
                break;
            }
        }
        if ($route === null || !app('router')->has($route)) {
            return null;
        }
        return app('laravellocalization')->getLocalizedURL($locale, route($route), [], true);
    }

    private function bindingPaths(array $binding, string $locale): array
    {
        $key = json_encode($binding) . (isset($binding['inline_templates']) ? '' : ':' . $locale);
        if (isset($this->bindings[$key])) {
            return $this->bindings[$key];
        }
        $paths = [];
        if (isset($binding['inline_templates'])) {
            $file = $binding['inline_templates'];
            $paths = $this->inlineTemplates($file);
            if ($this->persist) {
                foreach ($this->inlineTemplateData(public_path($file)) as $data) {
                    $path = $this->inlinePath($data);
                    if ($path === null) {
                        throw new RuntimeException('Unsupported inline template icon.');
                    }
                    $target = storage_path('app/public/' . rawurldecode(substr($path, 9)));
                    if (!is_file($target)) {
                        $bytes = base64_decode(explode(',', $data, 2)[1], true);
                        if ($bytes === false || @getimagesizefromstring($bytes) === false) {
                            throw new RuntimeException('Invalid inline image bytes.');
                        }
                        if (!is_dir(dirname($target))) {
                            mkdir(dirname($target), 0755, true);
                        }
                        file_put_contents($target, $bytes, LOCK_EX);
                    }
                }
            }
            return $this->cacheBinding($key, $paths);
        }
        if (isset($binding['sources'])) {
            foreach ($binding['sources'] as $source) {
                $paths = array_merge($paths, $this->bindingPaths($source, $locale));
            }
            return $this->cacheBinding($key, $paths);
        }
        if (isset($binding['glob'])) {
            foreach (glob(public_path($binding['glob'])) ?: [] as $file) {
                $paths[] = '/' . str_replace('\\', '/', substr($file, strlen(public_path()) + 1));
            }
            return $this->cacheBinding($key, $paths);
        }
        $class = $binding['model'];
        $query = $class::query();
        foreach ($binding['where'] ?? [] as $column => $value) {
            $query->where($column, is_string($value) ? str_replace('{locale}', $locale, $value) : $value);
        }
        // PDO's buffered cursor can retain every row (including large JSON
        // columns) before the first model is processed.
        foreach ($query->lazyById(25, $query->getModel()->getQualifiedKeyName(), $query->getModel()->getKeyName()) as $model) {
            $modelContext = [];
            foreach (['title', 'name', 'shortname'] as $textField) {
                $value = method_exists($model, 'getTranslatedAttribute')
                    ? $model->getTranslatedAttribute($textField, $locale) : $model->getAttribute($textField);
                if (is_string($value) && trim(strip_tags($value)) !== '') {
                    $modelContext['page_title'] = mb_substr(trim(strip_tags($value)), 0, 200);
                    break;
                }
            }
            $fields = $binding['fields'] ?? [];
            if (isset($binding['preferred_fields'])) {
                $fields = [];
                foreach ($binding['preferred_fields'] as $field) {
                    if ($model->getAttribute($field)) {
                        $fields = [$field];
                        break;
                    }
                }
            }
            foreach ($fields as $field) {
                $field = str_replace('{locale}', $locale, $field);
                $value = method_exists($model, 'getTranslatedAttribute')
                    ? $model->getTranslatedAttribute($field, $locale) : $model->getAttribute($field);
                $values = !empty($binding['csv']) && is_string($value) ? explode(',', $value) : $this->paths($value);
                foreach ($values as $path) {
                    if (in_array(strtolower(pathinfo((string) parse_url($path, PHP_URL_PATH), PATHINFO_EXTENSION)), $binding['exclude_extensions'] ?? [], true)) {
                        continue;
                    }
                    $resolved = !empty($binding['order_image']) ? order_image_url($path)
                        : (preg_match('#^(?:https?://|/)#i', $path) ? $path : '/' . ($binding['prefix'] ?? 'storage/') . $path);
                    if (!empty($binding['image_original'])) {
                        $resolved = image_original_url($resolved);
                    }
                    $paths[] = $modelContext + ['path' => $resolved,
                        'owner_type' => $class, 'owner_id' => $model->getKey(), 'owner_field' => $field, 'owner_path' => $path];
                }
            }
            foreach ($binding['json_images'] ?? [] as $field) {
                $data = $model->getAttribute($field);
                $data = is_string($data) ? json_decode($data, true) : $data;
                foreach ($this->orderPaths((array) $data, $binding) as $path) {
                    $paths[] = $modelContext + ['path' => !empty($binding['raw_asset']) ? asset($path) : order_image_url($path), 'owner_type' => $class,
                        'owner_id' => $model->getKey(), 'owner_field' => $field, 'owner_path' => $path];
                }
            }
            foreach ($binding['media'] ?? [] as $collection) {
                foreach ($model->getMedia($collection) as $media) {
                    $paths[] = $modelContext + ['path' => $media->getUrl(), 'owner_type' => $class,
                        'owner_id' => $model->getKey(), 'owner_field' => 'media:' . $collection, 'owner_path' => $media->getUrl()];
                }
            }
        }
        return $this->cacheBinding($key, $paths);
    }

    private function cacheBinding(string $key, array $paths): array
    {
        // Serialized bytes provide a stable budget; PHP arrays occupy several times this size.
        // Serializing a large path list temporarily duplicates it in memory.
        // A conservative estimate is enough for an eviction budget.
        $size = count($paths) * 256;
        $budget = max(0, (int) config('frontend_alt.binding_cache_bytes', 16777216));
        if ($size > $budget) {
            return $paths;
        }
        while ($this->bindings && ($this->bindingBytes + $size > $budget || count($this->bindings) >= 128)) {
            $oldest = array_key_first($this->bindings);
            $this->bindingBytes -= $this->bindingSizes[$oldest];
            unset($this->bindings[$oldest], $this->bindingSizes[$oldest]);
        }
        $this->bindings[$key] = $paths;
        $this->bindingSizes[$key] = $size;
        $this->bindingBytes += $size;
        return $paths;
    }

    private function orderPaths(array $data, array $binding): array
    {
        $paths = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $binding['image_keys'] ?? ['activeImage', 'savedImage', 'orig_images'], true)) {
                $values = $this->paths($value);
                if ($key === 'orig_images' && isset($binding['orig_slice'])) {
                    $values = array_slice($values, $binding['orig_slice'][0], $binding['orig_slice'][1] ?? null);
                }
                $paths = array_merge($paths, $values);
            } elseif (is_array($value)) {
                $paths = array_merge($paths, $this->orderPaths($value, $binding));
            }
        }
        return $paths;
    }

    private function paths($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return trim($value) !== '' ? [$value] : [];
            }
            $value = $decoded;
        }
        if (!is_array($value)) {
            return [];
        }
        if (isset($value['download_link'])) {
            return [(string) $value['download_link']];
        }
        $paths = [];
        foreach ($value as $item) {
            $paths = array_merge($paths, $this->paths($item));
        }
        return $paths;
    }
}
