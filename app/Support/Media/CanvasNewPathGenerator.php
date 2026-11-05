<?php

namespace App\Support\Media;

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

class CanvasNewPathGenerator extends DefaultPathGenerator
{
    public function getPath(Media $media): string
    {
        $legacy = 'canvasnews/media/'.$media->getKey().'/';
        $standard = parent::getPath($media);
        $disk = Storage::disk($media->disk);

        // Existing Laravel 13 uploads may already use the standard ID directory.
        // Prefer the original location when both copies exist; never move files.
        if (! $disk->exists($legacy.$media->file_name) && $disk->exists($standard.$media->file_name)) {
            return $standard;
        }

        return $legacy;
    }

    // The legacy generator only changed original paths. Conversions and
    // responsive images retain the inherited ID-based directories.
}
