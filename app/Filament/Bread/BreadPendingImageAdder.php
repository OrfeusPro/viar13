<?php

namespace App\Filament\Bread;

use Closure;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/** Scoped to BREAD media transactions: old files retire only after commit. */
class BreadPendingImageAdder extends FileAdder
{
    public ?Closure $onPendingMedia = null;

    protected function processMediaItem(HasMedia $model, Media $media, FileAdder $fileAdder): void
    {
        // Keep the object even if copying/conversions throw before the adder returns.
        // Its assigned ID and model remain available to rollback cleanup.
        $media->setRelation('model', $model);
        $this->onPendingMedia?->__invoke($media);
        parent::processMediaItem($model, $media, $fileAdder);
    }

    protected function getMediaCollection(string $collectionName): ?MediaCollection
    {
        $collection = parent::getMediaCollection($collectionName);
        if ($collection) {
            $collection = clone $collection;
            $collection->collectionSizeLimit = false;
        }

        return $collection;
    }
}
