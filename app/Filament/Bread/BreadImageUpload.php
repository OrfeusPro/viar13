<?php

namespace App\Filament\Bread;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BreadImageUpload
{
    public function mediaBytes(UploadedFile $file, string $path): string
    {
        return $this->mediaVariants($file, $path)[''];
    }

    /** Standalone Voyager media details use type/width/height, unlike BREAD thumbnails. */
    public function mediaVariants(UploadedFile $file, string $path, array $details = []): array
    {
        $bytes = file_get_contents($file->getRealPath());
        $mime = $file->getMimeType();
        // Voyager media upload preserves GIF and does not process WebP/video/documents.
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/bmp', 'image/svg+xml'], true)) {
            return ['' => $bytes];
        }
        $this->validateMediaDetails($details, $path);
        $info = @getimagesizefromstring($bytes);
        if (! extension_loaded('gd') || ! $info) { $this->fail($path, 'Не удалось обработать изображение.'); }
        $this->guardMemory($info[0] * $info[1], 0, $path);
        $source = @imagecreatefromstring($bytes);
        if (! $source) { $this->fail($path, 'Не удалось прочитать изображение.'); }
        try {
            if ($mime === 'image/jpeg') { $source = $this->orient($source, $file); }
            $outputMime = match (strtolower($file->getClientOriginalExtension())) {
                'jpg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif',
                'bmp' => 'image/bmp', 'webp' => 'image/webp', default => $mime,
            };
            $quality = (int) ($details['quality'] ?? 90);
            $watermark = isset($details['watermark']['source']) ? $details['watermark'] : null;
            $variants = [];
            foreach ($details['thumbnails'] ?? [] as $thumbnail) {
                $thumb = $this->mediaThumbnail($source, $thumbnail, $path);
                try {
                    $variants['-'.($thumbnail['name'] ?? 'thumbnail')] = $this->render($thumb, null, null,
                        false, false, $outputMime, $quality, $path, ($thumbnail['watermark'] ?? false) ? $watermark : null);
                } finally { imagedestroy($thumb); }
            }
            $variants[''] = $this->render($source, null, null, false, false, $outputMime, $quality, $path, $watermark);
            return $variants;
        } finally { imagedestroy($source); }
    }

    private function validateMediaDetails(array $details, string $path): void
    {
        if (isset($details['watermark']) && ! is_array($details['watermark'])) { $this->fail($path, 'Некорректные настройки водяного знака.'); }
        if (isset($details['quality']) && (filter_var($details['quality'], FILTER_VALIDATE_INT) === false || $details['quality'] < 1 || $details['quality'] > 100)) {
            $this->fail($path, 'Качество изображения должно быть от 1 до 100.');
        }
        $thumbnails = $details['thumbnails'] ?? [];
        if (! is_array($thumbnails) || ! array_is_list($thumbnails) || count($thumbnails) > 20) {
            $this->fail($path, 'Некорректные настройки миниатюр.');
        }
        $names = [];
        foreach ($thumbnails as $thumb) {
            if (! is_array($thumb) || ! in_array($thumb['type'] ?? 'fit', ['fit', 'crop', 'resize'], true)
                || ! is_string($thumb['name'] ?? 'thumbnail') || ! preg_match('/^[\pL\pN_-]{1,80}$/u', $thumb['name'] ?? 'thumbnail')
                || in_array($thumb['name'] ?? 'thumbnail', $names, true)) { $this->fail($path, 'Некорректные настройки миниатюр.'); }
            $names[] = $thumb['name'] ?? 'thumbnail';
            foreach (['width', 'height'] as $key) {
                $required = $key === 'width' ? ($thumb['type'] ?? 'fit') !== 'resize' : ($thumb['type'] ?? 'fit') === 'crop';
                if (($required && ! isset($thumb[$key])) || (isset($thumb[$key])
                    && (filter_var($thumb[$key], FILTER_VALIDATE_INT) === false || $thumb[$key] < 1 || $thumb[$key] > 8192))) {
                    $this->fail($path, 'Размер миниатюры должен быть от 1 до 8192 px.');
                }
            }
            if (! isset($thumb['width']) && ! isset($thumb['height'])) { $this->fail($path, 'Укажите размер миниатюры.'); }
            foreach (['x', 'y'] as $key) {
                if (isset($thumb[$key]) && (filter_var($thumb[$key], FILTER_VALIDATE_INT) === false || $thumb[$key] < 0)) {
                    $this->fail($path, 'Некорректные координаты миниатюры.');
                }
            }
            if (isset($thumb['position'])) {
                if (! is_string($thumb['position'])) { $this->fail($path, 'Некорректное положение миниатюры.'); }
                $this->mediaPosition($thumb['position'], 0, 0, 0, 0, 0, 0, $path);
            }
        }
        if (isset($details['watermark']['source'])) {
            $watermark = $details['watermark'];
            if (! is_string($watermark['source']) || (isset($watermark['size']) && (! is_numeric($watermark['size']) || $watermark['size'] <= 0 || $watermark['size'] > 1000))) {
                $this->fail($path, 'Некорректные настройки водяного знака.');
            }
            foreach (['x', 'y'] as $key) {
                if (isset($watermark[$key]) && (filter_var($watermark[$key], FILTER_VALIDATE_INT) === false || $watermark[$key] < 0)) {
                    $this->fail($path, 'Некорректные отступы водяного знака.');
                }
            }
            if (isset($watermark['position']) && ! is_string($watermark['position'])) { $this->fail($path, 'Некорректное положение водяного знака.'); }
            $this->mediaPosition($watermark['position'] ?? 'top-left', 0, 0, 0, 0, 0, 0, $path);
        }
    }

    private function mediaPosition(string $position, int $width, int $height, int $innerWidth, int $innerHeight, int $x, int $y, string $path): array
    {
        return match (strtolower($position)) {
            'top-left', 'left-top' => [$x, $y],
            'top', 'top-center', 'top-middle', 'center-top', 'middle-top' => [intdiv($width, 2) - intdiv($innerWidth, 2), $y],
            'top-right', 'right-top' => [$width - $innerWidth - $x, $y],
            'left', 'left-center', 'left-middle', 'center-left', 'middle-left' => [$x, intdiv($height, 2) - intdiv($innerHeight, 2)],
            'right', 'right-center', 'right-middle', 'center-right', 'middle-right' => [$width - $innerWidth - $x, intdiv($height, 2) - intdiv($innerHeight, 2)],
            'bottom-left', 'left-bottom' => [$x, $height - $innerHeight - $y],
            'bottom', 'bottom-center', 'bottom-middle', 'center-bottom', 'middle-bottom' => [intdiv($width, 2) - intdiv($innerWidth, 2), $height - $innerHeight - $y],
            'bottom-right', 'right-bottom' => [$width - $innerWidth - $x, $height - $innerHeight - $y],
            'center', 'middle', 'center-center', 'middle-middle' => [intdiv($width, 2) - intdiv($innerWidth, 2) + $x, intdiv($height, 2) - intdiv($innerHeight, 2) + $y],
            default => $this->fail($path, 'Неизвестное положение изображения.'),
        };
    }

    private function mediaThumbnail(\GdImage $source, array $options, string $path): \GdImage
    {
        $sw = imagesx($source); $sh = imagesy($source);
        $type = $options['type'] ?? 'fit';
        $width = $options['width'] ?? null; $height = $options['height'] ?? null;
        $x = $y = 0; $cw = $sw; $ch = $sh;
        if ($type === 'resize') {
            $ratio = min($width !== null ? $width / $sw : INF, $height !== null ? $height / $sh : INF);
            if (! ($options['upsize'] ?? true)) { $ratio = min(1, $ratio); }
            $width = max(1, (int) round($sw * $ratio)); $height = max(1, (int) round($sh * $ratio));
        } elseif ($type === 'fit') {
            $height ??= $width;
            $ratio = min($sw / $width, $sh / $height);
            $cw = max(1, (int) round($width * $ratio)); $ch = max(1, (int) round($height * $ratio));
            [$x, $y] = $this->mediaPosition($options['position'] ?? 'center', $sw, $sh, $cw, $ch, 0, 0, $path);
        } else {
            $cw = $width; $ch = $height;
            if (! isset($options['x']) && ! isset($options['y'])) {
                [$x, $y] = $this->mediaPosition('center', $sw, $sh, $cw, $ch, 0, 0, $path);
            } else { $x = (int) ($options['x'] ?? 0); $y = (int) ($options['y'] ?? 0); }
        }
        return $this->mediaResample($source, $x, $y, $width, $height, $cw, $ch, $path);
    }

    private function mediaResample(\GdImage $source, int $x, int $y, int $width, int $height, int $cw, int $ch, string $path): \GdImage
    {
        $this->guardMemory(imagesx($source) * imagesy($source), $width * $height, $path);
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false); imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        if (! imagecopyresampled($image, $source, 0, 0, $x, $y, $width, $height, $cw, $ch)) {
            imagedestroy($image); $this->fail($path, 'Не удалось создать миниатюру.');
        }
        return $image;
    }

    private function mediaWatermark(\GdImage $image, array $options, string $path): void
    {
        app(BreadLibraryFiles::class)->checkedFile($options['source']);
        $disk = Storage::disk('public');
        if ($disk->size($options['source']) > 10 * 1024 * 1024) { $this->fail($path, 'Водяной знак превышает 10 МБ.'); }
        $bytes = $disk->get($options['source']);
        $info = @getimagesizefromstring($bytes);
        if (! $info) { $this->fail($path, 'Не удалось прочитать водяной знак.'); }
        $this->guardMemory($info[0] * $info[1], 0, $path);
        $mark = @imagecreatefromstring($bytes);
        if (! $mark) { $this->fail($path, 'Не удалось прочитать водяной знак.'); }
        $resized = null;
        try {
            // Intervention keeps the fractional width while computing the height;
            // GD then truncates the width when creating the destination canvas.
            $rawWidth = imagesx($image) * ($options['size'] ?? 15) / 100;
            $resized = $this->mediaResample($mark, 0, 0, max(1, (int) $rawWidth),
                max(1, (int) round($rawWidth * imagesy($mark) / imagesx($mark))), imagesx($mark), imagesy($mark), $path);
            [$x, $y] = $this->mediaPosition($options['position'] ?? 'top-left', imagesx($image), imagesy($image), imagesx($resized), imagesy($resized), (int) ($options['x'] ?? 0), (int) ($options['y'] ?? 0), $path);
            imagealphablending($image, true);
            if (! imagecopy($image, $resized, $x, $y, 0, 0, imagesx($resized), imagesy($resized))) { $this->fail($path, 'Не удалось добавить водяной знак.'); }
        } finally { if ($resized) { imagedestroy($resized); } imagedestroy($mark); }
    }

    private function orient(\GdImage $source, UploadedFile $file): \GdImage
    {
        if (function_exists('exif_read_data')) {
                $orientation = (@exif_read_data($file->getRealPath())['Orientation']) ?? 1;
                if (in_array($orientation, [2, 4, 5, 7], true)) { imageflip($source, in_array($orientation, [4, 5], true) ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL); }
                $angle = match ($orientation) { 3 => 180, 5, 6, 7 => -90, 8 => 90, default => 0 };
                if ($angle) { $rotated = imagerotate($source, $angle, 0); imagedestroy($source); $source = $rotated; }
            }
        return $source;
    }

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

    private function render(\GdImage $source, ?int $width, ?int $height, bool $preventUpsize, bool $crop, string $mime, int $quality, string $path, ?array $watermark = null): string
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
            if ($watermark !== null) { $this->mediaWatermark($image, $watermark, $path); }
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
            if ($mime === 'image/jpeg') { $source = $this->orient($source, $file); }
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
