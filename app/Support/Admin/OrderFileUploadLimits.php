<?php

namespace App\Support\Admin;

final class OrderFileUploadLimits
{
    /** FilePond's KB strings use 1000 bytes; Laravel's file rules use 1024. */
    public static function alpineAttributes(int $maxKiB): array
    {
        $bytes = $maxKiB * 1024;

        return ['x-init' => "\$watch('pond', value => { if (value) value.setOptions({ maxFileSize: {$bytes} }); }); if (pond) pond.setOptions({ maxFileSize: {$bytes} });"];
    }
}
