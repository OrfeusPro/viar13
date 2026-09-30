<?php

namespace App\Filament\Bread;

use Illuminate\Validation\ValidationException;

class BreadImageCrop
{
    public function crop(string $bytes, int $x, int $y, int $width, int $height, int $quality = 90): array
    {
        $info = @getimagesizefromstring($bytes);
        $mime = $info['mime'] ?? '';
        $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/bmp' => 'bmp'][$mime] ?? null;
        $fail = static fn (string $message) => throw ValidationException::withMessages(['libraryCrop' => $message]);
        if (! extension_loaded('gd') || ! $extension) {
            $fail('Обрезка доступна для JPEG, PNG, WebP и BMP при включённом GD.');
        }
        if ($x < 0 || $y < 0 || $width < 1 || $height < 1 || $x + $width > $info[0] || $y + $height > $info[1]) {
            $fail('Область обрезки выходит за границы изображения.');
        }
        // Limit decoded memory, not just compressed upload size.
        $pixels = $info[0] * $info[1];
        $memoryLimit = ini_get('memory_limit');
        $limit = (int) $memoryLimit;
        $limit *= match (strtolower(substr($memoryLimit, -1))) { 'g' => 1024 ** 3, 'm' => 1024 ** 2, 'k' => 1024, default => 1 };
        $required = ($pixels + $width * $height) * 8 + 16 * 1024 ** 2;
        if ($pixels > 20_000_000 || ($limit > 0 && memory_get_usage(true) + $required > $limit)) {
            $fail('Изображение слишком большое для обрезки на сервере.');
        }
        $image = @imagecreatefromstring($bytes);
        if (! $image) {
            $fail('Не удалось прочитать изображение.');
        }
        $cropped = null;
        try {
            $cropped = imagecrop($image, compact('x', 'y', 'width', 'height'));
            if (! $cropped) {
                $fail('Не удалось обрезать изображение.');
            }
            imagesavealpha($cropped, true);
            ob_start();
            try {
                $written = match ($mime) {
                    'image/jpeg' => imagejpeg($cropped, null, max(1, min(100, $quality))),
                    'image/png' => imagepng($cropped),
                    'image/webp' => imagewebp($cropped, null, max(1, min(100, $quality))),
                    'image/bmp' => imagebmp($cropped),
                };
                $output = ob_get_contents();
            } finally {
                ob_end_clean();
            }
            if (! $written || ! $output) {
                $fail('Не удалось сохранить обрезанное изображение.');
            }

            return ['bytes' => $output, 'extension' => $extension];
        } finally {
            if ($cropped) imagedestroy($cropped);
            imagedestroy($image);
        }
    }
}
