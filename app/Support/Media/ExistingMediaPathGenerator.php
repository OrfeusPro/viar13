<?php

namespace App\Support\Media;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

class ExistingMediaPathGenerator extends DefaultPathGenerator
{
    public function getPath(Media $media): string
    {
        $modelType = Relation::getMorphedModel($media->model_type) ?? $media->model_type;
        $directory = Str::plural(mb_strtolower(class_basename($modelType)));
        $legacy = $directory.'/media/'.$media->getKey().'/';

        // Match the original application only when the actual file is present.
        if (Storage::disk($media->disk)->exists($legacy.$media->file_name)) {
            return $legacy;
        }

        return parent::getPath($media);
    }
}
