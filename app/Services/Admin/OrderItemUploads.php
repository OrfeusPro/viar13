<?php

namespace App\Services\Admin;

use App\Models\Orders;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OrderItemUploads
{
    public const MAX_KB = 102400;

    private const TYPES = [
        'png' => ['image/png'], 'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'],
        'bmp' => ['image/bmp', 'image/x-ms-bmp'], 'gif' => ['image/gif'], 'webp' => ['image/webp'],
        'avif' => ['image/avif'], 'tif' => ['image/tiff'], 'tiff' => ['image/tiff'],
        'heic' => ['image/heic', 'image/heif', 'image/heic-sequence'],
        'heif' => ['image/heif', 'image/heic', 'image/heif-sequence'],
        'pdf' => ['application/pdf'], 'psd' => ['image/vnd.adobe.photoshop', 'image/x-photoshop'],
        'fig' => ['application/octet-stream', 'application/zip', 'application/x-fig', 'application/x-xfig', 'image/x-xfig'],
    ];

    public static function acceptedTypes(bool $attachments = false): array
    {
        $types = self::TYPES;
        if (! $attachments) {
            unset($types['pdf'], $types['psd'], $types['fig']);
        }

        return array_values(array_unique(array_merge(...array_values($types))));
    }

    public function validate(array $files, bool $attachments): void
    {
        validator(['files' => $files], [
            'files' => ['array', 'max:30'],
            'files.*' => ['required', 'file', 'max:'.self::MAX_KB, function ($attribute, $value, $fail) use ($attachments): void {
                if (! $value instanceof UploadedFile) {
                    return;
                }
                $extension = mb_strtolower($value->getClientOriginalExtension());
                $allowed = self::TYPES[$extension] ?? [];
                if ((! $attachments && in_array($extension, ['pdf', 'psd', 'fig'], true))
                    || ! in_array($value->getMimeType(), $allowed, true)) {
                    $fail('Неподдерживаемый тип файла.');
                }
            }],
        ])->validate();
    }

    /** @param array<int, string> $createdPaths Only new paths are eligible for rollback. */
    public function store(Orders $order, array $item, array $files, array &$createdPaths): array
    {
        if ($files === []) {
            return [];
        }
        $disk = Storage::disk('uploads');
        $base = Orders::generateImageName($order, $item, $item['count'] ?? 1);
        $urls = [];
        $sequence = 1;
        foreach ($files as $file) {
            $extension = mb_strtolower($file->getClientOriginalExtension());
            do {
                $filename = $base.'_'.($sequence++).'.'.$extension;
                $path = 'orders/'.$filename;
            } while ($disk->exists($path));
            // Reserve before the write, so a failed partial write can also be removed.
            $createdPaths[] = $path;
            if (! $disk->putFileAs('orders', $file, $filename)) {
                throw ValidationException::withMessages(['files' => 'Не удалось сохранить исходник позиции.']);
            }
            $urls[] = url('/'.$path);
        }

        return $urls;
    }

    public function rollback(array $createdPaths): void
    {
        foreach ($createdPaths as $path) {
            Storage::disk('uploads')->delete($path);
        }
    }
}
