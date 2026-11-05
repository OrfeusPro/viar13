<?php

namespace App\Services\AltGeneration;

use App\Models\ImageAltSuggestion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class AltWebpInfo
{
    public function stats($query): array
    {
        $baseQuery = clone $query;
        $baseQuery->getQuery()->orders = null;
        $baseQuery->getQuery()->unionOrders = null;

        $paths = $baseQuery
            ->whereNotNull('image_path')
            ->where('image_path', '<>', '')
            ->select('image_path')
            ->groupBy('image_path')
            ->pluck('image_path')
            ->filter()
            ->values()
            ->all();

        $total = count($paths);
        $withWebp = 0;

        foreach ($this->webpInfoForPaths($paths) as $info) {
            if (! empty($info['exists'])) {
                $withWebp++;
            }
        }

        return [
            'total' => $total,
            'with_webp' => $withWebp,
            'without_webp' => max(0, $total - $withWebp),
            'coverage_percent' => $total > 0 ? round(($withWebp / $total) * 100, 1) : 0.0,
        ];
    }

    /**
     * @param  Builder  $query
     */
    public function filter($query, bool $mustHaveWebp): void
    {
        $baseQuery = clone $query;
        $baseQuery->getQuery()->orders = null;
        $baseQuery->getQuery()->unionOrders = null;

        $paths = $baseQuery
            ->whereNotNull('image_path')
            ->where('image_path', '<>', '')
            ->select('image_path')
            ->groupBy('image_path')
            ->pluck('image_path')
            ->filter()
            ->values()
            ->all();

        $matchingPaths = [];

        foreach ($this->webpInfoForPaths($paths) as $path => $info) {
            $exists = ! empty($info['exists']);

            if (($mustHaveWebp && $exists) || (! $mustHaveWebp && ! $exists)) {
                $matchingPaths[] = $path;
            }
        }

        if (empty($matchingPaths)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('image_path', array_values(array_unique($matchingPaths)));
    }

    /**
     * @param  array<int, mixed>  $paths
     * @return array<string, array{exists: bool, path: string|null, url: string|null, original_path: string, original_is_webp: bool}>
     */
    private function webpInfoForPaths(array $paths): array
    {
        $result = [];

        foreach ($paths as $path) {
            $path = trim((string) $path);

            if ($path === '') {
                continue;
            }

            $result[$path] = $this->info($path);
        }

        return $result;
    }

    /**
     * @return array{exists: bool, path: string|null, url: string|null, original_path: string, original_is_webp: bool}
     */
    public function info(string $imagePath): array
    {
        $originalPath = $imagePath;
        $storagePath = $this->normalizeStorageImagePath($imagePath);
        $webpPath = $this->webpPathFromStoragePath($storagePath);
        $exists = $webpPath !== null && Storage::disk('public')->exists($webpPath);

        return [
            'exists' => $exists,
            'path' => $webpPath,
            'url' => $webpPath !== null ? Storage::disk('public')->url($webpPath) : null,
            'original_path' => $originalPath,
            'original_is_webp' => strtolower((string) pathinfo($storagePath, PATHINFO_EXTENSION)) === 'webp',
        ];
    }

    private function normalizeStorageImagePath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = trim(str_replace('\\\\', '/', $path));

        if ($path === '' || strpos($path, 'data:image/') === 0) {
            return null;
        }

        $parts = preg_split('/[?#]/', $path, 2);
        $path = $parts[0] ?? $path;

        if (preg_match('#^https?://#i', $path)) {
            $parsedPath = parse_url($path, PHP_URL_PATH);
            $path = is_string($parsedPath) ? $parsedPath : '';
        }

        $path = ltrim($path, '/');

        if (strpos($path, 'public/storage/') === 0) {
            $path = substr($path, 15);
        }

        if (strpos($path, 'storage/') === 0) {
            $path = substr($path, 8);
        }

        return trim($path) === '' ? null : $path;
    }

    private function webpPathFromStoragePath(?string $storagePath): ?string
    {
        if ($storagePath === null || trim($storagePath) === '') {
            return null;
        }

        $storagePath = trim(str_replace('\\\\', '/', $storagePath));
        $extension = pathinfo($storagePath, PATHINFO_EXTENSION);

        if ($extension === '') {
            return null;
        }

        if (strtolower($extension) === 'webp') {
            return $storagePath;
        }

        return preg_replace('/\\.[^\\.\\/]+$/', '.webp', $storagePath) ?: null;
    }

}
