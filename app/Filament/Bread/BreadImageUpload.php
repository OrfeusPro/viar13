<?php

namespace App\Filament\Bread;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BreadImageUpload
{
    private function fail(string $path, string $message): never
    {
        throw ValidationException::withMessages([$path => $message]);
    }

    public function validate(array $details): void
    {
        $bad = fn () => $this->fail('details', 'Проверьте resize, quality, upsize, thumbnails и preserveFileUploadName.');
        foreach (['upsize', 'preserveFileUploadName'] as $key) { if (isset($details[$key]) && ! is_bool($details[$key])) { $bad(); } }
        if (isset($details['quality']) && ((! is_int($details['quality']) && ! is_string($details['quality'])) || ! preg_match('/^\d{1,3}%?$/', (string) $details['quality']) || (int) $details['quality'] < 1 || (int) $details['quality'] > 100)) { $bad(); }
        if (isset($details['resize'])) {
            if (! is_array($details['resize']) || array_diff(array_keys($details['resize']), ['width', 'height']) !== []) { $bad(); }
            foreach ($details['resize'] as $size) { if ($size !== null && (filter_var($size, FILTER_VALIDATE_INT) === false || $size < 1 || $size > 8192)) { $bad(); } }
        }
        if (isset($details['thumbnails'])) {
            if (! is_array($details['thumbnails']) || ! array_is_list($details['thumbnails']) || count($details['thumbnails']) > 20) { $bad(); }
            $names = [];
            foreach ($details['thumbnails'] as $thumb) {
                if (! is_array($thumb) || ! is_string($thumb['name'] ?? null) || ! preg_match('/^[a-zA-Z0-9_-]{1,80}$/', $thumb['name']) || $thumb['name'] === 'static' || in_array($thumb['name'], $names, true)) { $bad(); }
                $names[] = $thumb['name'];
                if (isset($thumb['scale'])) {
                    if (filter_var($thumb['scale'], FILTER_VALIDATE_INT) === false || $thumb['scale'] < 1 || $thumb['scale'] > 1000) { $bad(); }
                } else {
                    if (! is_array($thumb['crop'] ?? null)) { $bad(); }
                    foreach (['width', 'height'] as $key) { if (filter_var($thumb['crop'][$key] ?? null, FILTER_VALIDATE_INT) === false || $thumb['crop'][$key] < 1 || $thumb['crop'][$key] > 8192) { $bad(); } }
                }
            }
        }
    }

    private function render(\GdImage $source, ?int $width, ?int $height, bool $preventUpsize, bool $crop, string $mime, int $quality, string $path): string
    {
        $sw = imagesx($source); $sh = imagesy($source);
        $ratio = $crop ? max(($width ?? $sw) / $sw, ($height ?? $sh) / $sh) : min($width !== null ? $width / $sw : INF, $height !== null ? $height / $sh : INF);
        if (is_infinite($ratio)) { $ratio = 1; }
        if ($preventUpsize && ! $crop) { $ratio = min(1, $ratio); }
        $tw = $crop ? $width : max(1, (int) round($sw * $ratio)); $th = $crop ? $height : max(1, (int) round($sh * $ratio));
        $this->guardMemory($sw * $sh, $tw * $th, $path);
        $image = imagecreatetruecolor($tw, $th);
        try {
            imagealphablending($image, false); imagesavealpha($image, true);
            imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
            $cw = $crop ? $tw / $ratio : $sw; $ch = $crop ? $th / $ratio : $sh;
            imagecopyresampled($image, $source, 0, 0, (int) (($sw - $cw) / 2), (int) (($sh - $ch) / 2), $tw, $th, (int) $cw, (int) $ch);
            ob_start();
            try {
                $ok = match ($mime) {
                    'image/jpeg' => imagejpeg($image, null, $quality), 'image/png' => imagepng($image),
                    'image/webp' => imagewebp($image, null, $quality), 'image/gif' => imagegif($image),
                    'image/bmp' => imagebmp($image), 'image/avif' => imageavif($image, null, $quality),
                };
                $bytes = ob_get_contents();
            } finally { ob_end_clean(); }
            if (! $ok || ! $bytes) { $this->fail($path, 'Не удалось обработать изображение.'); }
            return $bytes;
        } finally { imagedestroy($image); }
    }

    private function guardMemory(int $sourcePixels, int $targetPixels, string $path): void
    {
        $limit = ini_get('memory_limit'); $bytes = (int) $limit * match (strtolower(substr($limit, -1))) { 'g' => 1024 ** 3, 'm' => 1024 ** 2, 'k' => 1024, default => 1 };
        if ($sourcePixels > 20_000_000 || $targetPixels > 20_000_000 || ($bytes > 0 && memory_get_usage(true) + ($sourcePixels + $targetPixels) * 8 + 16 * 1024 ** 2 > $bytes)) { $this->fail($path, 'Изображение слишком большое для обработки на сервере.'); }
    }

    protected function webpBytes(string $bytes, string $path): string
    {
        $info = @getimagesizefromstring($bytes);
        if (! $info) { throw new \RuntimeException('Invalid source for WebP companion.'); }
        $this->guardMemory($info[0] * $info[1], 0, $path);
        $image = @imagecreatefromstring($bytes);
        if (! $image) { throw new \RuntimeException('Cannot decode WebP companion source.'); }
        try {
            imagepalettetotruecolor($image); imagealphablending($image, false); imagesavealpha($image, true);
            ob_start();
            try { $written = imagewebp($image, null, 85); $output = ob_get_contents(); }
            finally { ob_end_clean(); }
            if (! $written || ! $output) { throw new \RuntimeException('Cannot encode WebP companion.'); }
            return $output;
        } finally { imagedestroy($image); }
    }

    public function store(UploadedFile $file, string $disk, string $directory, array $details, string $path, ?\Closure $onCreated = null): string
    {
        try { $this->validate($details); } catch (ValidationException) { $this->fail($path, 'Настройки обработки изображения несовместимы.'); }
        $bytes = file_get_contents($file->getRealPath()); $info = @getimagesizefromstring($bytes); $mime = $info['mime'] ?? '';
        $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif', 'image/bmp' => 'bmp', 'image/avif' => 'avif'][$mime] ?? null;
        if (! extension_loaded('gd') || ! $extension) { $this->fail($path, 'Для обработки нужны GD и поддерживаемый растровый файл.'); }
        $this->guardMemory($info[0] * $info[1], 0, $path);
        $source = @imagecreatefromstring($bytes);
        if (! $source) { $this->fail($path, 'Не удалось прочитать изображение.'); }
        try {
            if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
                $orientation = (@exif_read_data($file->getRealPath())['Orientation']) ?? 1;
                if (in_array($orientation, [2, 4, 5, 7], true)) { imageflip($source, in_array($orientation, [4, 5], true) ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL); }
                $angle = match ($orientation) { 3 => 180, 5, 6, 7 => -90, 8 => 90, default => 0 };
                if ($angle) { $rotated = imagerotate($source, $angle, 0); imagedestroy($source); $source = $rotated; }
            }
            $quality = (int) ($details['quality'] ?? 75); $prevent = isset($details['upsize']) && ! $details['upsize'];
            $width = isset($details['resize']['width']) ? (int) $details['resize']['width'] : null; $height = isset($details['resize']['height']) ? (int) $details['resize']['height'] : null;
            $main = $this->render($source, $width, $height, $prevent, false, $mime, $quality, $path);
            // Preserve every GIF: GD only decodes its first frame, and animations
            // may have no graphic-control block. A static derivative is explicit.
            $files = ['' => $mime === 'image/gif' ? $bytes : $main];
            if ($mime === 'image/gif') { $files['-static'] = $main; }
            foreach ($details['thumbnails'] ?? [] as $thumb) {
                $crop = ! isset($thumb['scale']);
                if ($crop) { $tw = (int) $thumb['crop']['width']; $th = (int) $thumb['crop']['height']; }
                else { $tw = max(1, (int) (($width ?? imagesx($source)) * $thumb['scale'] / 100)); $th = max(1, (int) (($height ?? imagesy($source)) * $thumb['scale'] / 100)); if ($width === null && $height !== null) { $tw = null; } elseif ($height === null && $width !== null) { $th = null; } }
                $files['-'.$thumb['name']] = $this->render($source, $tw, $th, $prevent, $crop, $mime, $quality, $path);
            }
            $storage = Storage::disk($disk); $directory = trim($directory, '/');
            $stem = ($details['preserveFileUploadName'] ?? false) ? trim(preg_replace('/[^\pL\pN ._-]/u', '', pathinfo(str_replace('\\', '/', $file->getClientOriginalName()), PATHINFO_FILENAME)), ' .') : Str::random(20);
            if ($stem === '') { $stem = Str::random(20); } $stem = mb_substr($stem, 0, 150);
            $counter = 0;
            $withWebp = function_exists('imagewebp') && in_array($extension, image_webp_supported_extensions(), true);
            do {
                $name = $stem.($counter ? $counter : ''); $counter++;
                $paths = array_map(fn ($suffix) => ($directory !== '' ? $directory.'/' : '').$name.$suffix.'.'.$extension, array_keys($files));
                $webpPath = $withWebp ? image_webp_path($paths[0]) : null;
            } while (collect($paths)->contains(fn ($candidate) => $storage->exists($candidate)) || ($webpPath !== null && $storage->exists($webpPath)));
            $created = [];
            try {
                foreach (array_values($files) as $i => $content) { $created[] = $paths[$i]; if (! $storage->put($paths[$i], $content, 'public')) { throw new \RuntimeException('Image storage write failed.'); } }
            } catch (\Throwable $error) { $storage->delete($created); report($error); $this->fail($path, 'Не удалось сохранить изображение и его миниатюры.'); }
            // Legacy companion generation is optional. Keep the original upload
            // and thumbnails if the codec or companion write fails.
            if ($webpPath !== null) {
                try {
                    $webp = $this->webpBytes($files[''], $path);
                    if (! $storage->put($webpPath, $webp, 'public')) { throw new \RuntimeException('WebP companion storage write failed.'); }
                    $created[] = $webpPath;
                } catch (\Throwable $error) {
                    // Only this new path was reserved; never remove another image.
                    try { $storage->delete($webpPath); } catch (\Throwable $cleanupError) { report($cleanupError); }
                    report($error);
                }
            }
            $onCreated?->__invoke($disk, $created);
            return $paths[0];
        } finally { imagedestroy($source); }
    }
}
