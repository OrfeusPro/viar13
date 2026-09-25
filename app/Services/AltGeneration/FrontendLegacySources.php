<?php

namespace App\Services\AltGeneration;

use App\Models\FrontendImage;
use App\Models\ImageAltSuggestion;
use Illuminate\Database\Eloquent\Model;

/** Read-only ownership lookup. Never copies or changes a legacy suggestion. */
class FrontendLegacySources
{
    private $paths = [];

    public function indexExisting(FrontendImageRegistry $registry): void
    {
        $this->paths = [];
        // MySQL/PDO may buffer a cursor's entire result set. Page by the
        // primary key so the ALT inventory can run with the default 128 MB.
        foreach (ImageAltSuggestion::query()->where('imageable_type', '<>', FrontendImage::class)
            ->where('image_path', 'not like', 'data:%')
            ->select(['id', 'imageable_type', 'imageable_id', 'field', 'image_path'])
            ->lazyById(25, 'id') as $row) {
            if (!is_string($row->imageable_type) || !$row->field || $row->imageable_id === null
                || !is_subclass_of($row->imageable_type, Model::class)) {
                continue;
            }
            $path = $registry->normalize($row->image_path);
            if ($path === null) {
                continue;
            }
            $source = ['type' => $row->imageable_type, 'id' => $row->imageable_id,
                'field' => $row->field, 'path' => $row->image_path];
            $key = $registry->suggestionPath($path);
            $identity = json_encode([$source['type'], (string) $source['id'], $source['field']]);
            $this->paths[$key][$identity] = $source;
        }
    }

    public function resolve(array $context, string $path, FrontendImageRegistry $registry): ?array
    {
        $configured = $this->fromContext($context, $path);
        $normalized = $registry->normalize($context['owner_path'] ?? $path);
        $matches = $normalized === null ? [] : ($this->paths[$registry->suggestionPath($normalized)] ?? []);
        if ($configured) {
            foreach ($matches as $match) {
                if ($match['type'] === $configured['type'] && (string) $match['id'] === (string) $configured['id']
                    && $match['field'] === $configured['field']) {
                    return $match;
                }
            }
            return $configured;
        }
        // A shared filename is not sufficient to choose between different owners/fields.
        return count($matches) === 1 ? reset($matches) : null;
    }

    public function fromContext(array $context, string $path): ?array
    {
        if (isset($context['legacy_source'])) {
            $source = $context['legacy_source'];
            if (is_array($source) && isset($source['type'], $source['id'], $source['field'], $source['path'])
                && $source['type'] !== FrontendImage::class && is_subclass_of($source['type'], Model::class)) {
                return $source;
            }
        }
        $class = $context['owner_type'] ?? null;
        $field = $context['owner_field'] ?? null;
        $target = config('alt_generation.targets', [])[$class ?? ''] ?? null;
        if (!$target || ($target['enabled'] ?? true) === false || $class === FrontendImage::class
            || !isset($context['owner_id']) || !is_string($field) || !is_subclass_of($class, Model::class)) {
            return null;
        }
        $fields = array_merge($target['image_fields'] ?? [], $target['gallery_fields'] ?? [],
            array_map(function ($collection) { return 'media:' . $collection; }, $target['media_collections'] ?? []));
        if (!in_array($field, $fields, true)) {
            return null;
        }
        return ['type' => $class, 'id' => $context['owner_id'], 'field' => $field,
            'path' => $context['owner_path'] ?? $path];
    }
}
