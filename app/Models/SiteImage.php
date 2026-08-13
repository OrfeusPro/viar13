<?php

namespace App\Models;

use App\Models\Concerns\HasAltSuggestions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use TCG\Voyager\Facades\Voyager;

class SiteImage extends Model
{
    use HasAltSuggestions;

    protected $table = 'site_images';

    // важно: добавь 'svg' в fillable
    protected $fillable = [
        'page',
        'position_name',
        'img',
        'svg',
        'img_alt',
        'img_title',
        'svg_alt',
        'svg_title',
        'is_show',
    ];

    protected $casts = ['is_show' => 'boolean'];

    protected $oldImgForCleanup = null;
    protected $oldSvgForCleanup = null;

    protected static function boot()
    {
        parent::boot();

        static::updating(function (self $m) {
            if ($m->isDirty('img')) $m->oldImgForCleanup = $m->getOriginal('img');
            if ($m->isDirty('svg')) $m->oldSvgForCleanup = $m->getOriginal('svg');
        });

        static::updated(function (self $m) {
            $m->ensureWebp();
            if ($m->oldImgForCleanup) { $m->deleteImagePair($m->oldImgForCleanup); $m->oldImgForCleanup = null; }
            if ($m->oldSvgForCleanup) { $m->deleteSingleFile($m->oldSvgForCleanup); $m->oldSvgForCleanup = null; }
        });

        static::created(function (self $m) { $m->ensureWebp(); });

        static::deleting(function (self $m) {
            if ($m->img) $m->deleteImagePair($m->img);
            if ($m->svg) $m->deleteSingleFile($m->svg);
        });
    }

    public function ensureWebp(): void
    {
        if (!$this->img) {
            return;
        }

        if (!function_exists('\\image_webp_url')) {
            return;
        }

        foreach ($this->extractPathsFromVoyagerFile((string) $this->img) as $path) {
            try {
                \image_webp_url('/storage/' . ltrim($path, '/'), true);
            } catch (\Throwable $e) {
                // Keep SiteImage saving safe if WebP generation is unavailable.
            }
        }
    }

    /**
     * Resolve the sibling alt column for an image source field.
     *
     * @param string $sourceField
     * @return string
     */
    public function altFieldFor(string $sourceField): string
    {
        return $sourceField . '_alt';
    }

    protected function deleteImagePair(string $value): void
    {
        $disk = config('voyager.storage.disk', config('filesystems.default', 'public'));
        $rel  = $this->toDiskRelativePath($value);
        if (!$rel) return;

        if (Storage::disk($disk)->exists($rel)) Storage::disk($disk)->delete($rel);

        $webpRel = $this->toWebpRelativePath($rel);
        if ($webpRel && Storage::disk($disk)->exists($webpRel)) Storage::disk($disk)->delete($webpRel);
    }

    // ВАЖНО: file-поле может быть JSON-массивом — удаляем все элементы
    protected function deleteSingleFile(string $value): void
    {
        $disk = config('voyager.storage.disk', config('filesystems.default', 'public'));

        foreach ($this->extractPathsFromVoyagerFile($value) as $rel) {
            if (Storage::disk($disk)->exists($rel)) {
                Storage::disk($disk)->delete($rel);
            }
        }
    }

    /* ==================== Нормализация путей ==================== */

    protected function toPublicUrl(string $value): string
    {
        $v = trim($value);
        if (Str::startsWith($v, ['http://', 'https://'])) {
            return function_exists('\\image_normalize_url_scheme') ? \image_normalize_url_scheme($v) : $v;
        }

        $v = ltrim($v, '/');
        $v = preg_replace('#/{2,}#', '/', $v);

        if (Str::startsWith($v, 'storage/')) {
            return function_exists('\\image_normalize_url_scheme') ? \image_normalize_url_scheme('/' . $v) : '/' . $v;
        }

        $url = '/storage/' . $v;

        return function_exists('\\image_normalize_url_scheme') ? \image_normalize_url_scheme($url) : $url;
    }

    protected function toDiskRelativePath(string $value): ?string
    {
        $v = trim($value);
        if (Str::startsWith($v, ['http://', 'https://'])) return null;

        // если это JSON — возьмём ПЕРВЫЙ файл
        $paths = $this->extractPathsFromVoyagerFile($v);
        if (!empty($paths)) return $paths[0];

        $v = ltrim($v, '/');
        $v = preg_replace('#/{2,}#', '/', $v);

        if (Str::startsWith($v, 'storage/')) return substr($v, 8);
        return $v;
    }

    protected function toWebpRelativePath(string $diskRelative): ?string
    {
        if (!preg_match('/\.(jpg|jpeg|png|gif|bmp)$/i', $diskRelative)) return null;
        return preg_replace('/\.(jpg|jpeg|png|gif|bmp)$/i', '.webp', $diskRelative);
    }

    protected function extractPathsFromVoyagerFile(string $value): array
    {
        $raw = trim($value);
        if (Str::startsWith($raw, '[')) {
            $arr = json_decode($raw, true) ?: [];
            $out = [];
            foreach ($arr as $item) {
                if (!empty($item['download_link'])) {
                    $p = ltrim($item['download_link'], '/');
                    $p = preg_replace('#/{2,}#', '/', $p);
                    if (Str::startsWith($p, 'storage/')) $p = substr($p, 8);
                    $out[] = $p;
                }
            }
            return $out;
        }

        $p = ltrim($raw, '/');
        $p = preg_replace('#/{2,}#', '/', $p);
        if ($p === '') return [];
        if (Str::startsWith($p, 'storage/')) $p = substr($p, 8);
        return [$p];
    }

    public function getImgUrlAttribute(): ?string
    {
        if (!$this->img) return null;
        if (Str::startsWith($this->img, ['http://', 'https://', '/storage/'])) {
            return $this->toPublicUrl($this->img);
        }
        $url = Voyager::image($this->img);

        return function_exists('\\image_normalize_url_scheme') ? \image_normalize_url_scheme($url) : $url;
    }

    public function getImgWebpUrlAttribute(): ?string
    {
        if (!$this->img) return null;
        $url = $this->toPublicUrl($this->img);
        if (function_exists('\\image_webp_url')) {
            try {
                return \image_webp_url($url);
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    public function getSvgUrlAttribute(): ?string
    {
        if (!$this->svg) return null;

        if (Str::startsWith($this->svg, ['http://', 'https://', '/storage/'])) {
            return $this->toPublicUrl($this->svg);
        }

        $paths = $this->extractPathsFromVoyagerFile((string)$this->svg);
        if (empty($paths)) return null;

        $url = Voyager::image($paths[0]);

        return function_exists('\\image_normalize_url_scheme') ? \image_normalize_url_scheme($url) : $url;
    }
}
