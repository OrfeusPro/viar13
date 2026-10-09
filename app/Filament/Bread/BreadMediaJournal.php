<?php

namespace App\Filament\Bread;

use Illuminate\Database\Connection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

/** Tracks only media created/retired by one BREAD transaction. */
class BreadMediaJournal
{
    private array $created = [];
    private array $retired = [];

    public function __construct(Connection $connection)
    {
        if ($connection->transactionLevel() < 1) { throw new \LogicException('BREAD media journal requires a transaction.'); }
        $connection->afterRollBack(function (): void {
            foreach ($this->created as $media) {
                if (! $media->getKey()) { continue; }
                try {
                    $generator = PathGeneratorFactory::create($media);
                    if (get_class($generator) === DefaultPathGenerator::class) {
                        // These directories belong to the new media ID. Include
                        // unfinished conversions not yet registered in metadata.
                        foreach (array_unique([$media->disk, $media->conversions_disk ?: $media->disk]) as $disk) {
                            if (! Storage::disk($disk)->deleteDirectory($generator->getPath($media))) {
                                report(new \RuntimeException('Failed to clean up pending BREAD media.'));
                            }
                        }
                    } else {
                        app(Filesystem::class)->removeAllFiles($media);
                    }
                } catch (\Throwable $error) { report($error); }
            }
        });
        $connection->afterCommit(function (): void {
            foreach ($this->retired as $media) {
                try { app(Filesystem::class)->removeAllFiles($media); }
                catch (\Throwable $error) { report($error); }
            }
        });
    }

    public function add($record, UploadedFile $file, string $field, array $properties = []): Media
    {
        $adder = app(BreadPendingImageAdder::class);
        $adder->onPendingMedia = function (Media $media): void { $this->created[] = $media; };

        return $adder->setSubject($record)->setFile($file->getRealPath())->preservingOriginal()
            ->usingFileName($file->getClientOriginalName())->withCustomProperties($properties)->toMediaCollection($field, 'public');
    }

    public function retire(Media $media): void
    {
        // Defer Spatie's filesystem observer; the row can roll back safely.
        $media->deleteQuietly();
        $this->retired[$media->getKey()] = $media;
    }

    public function enforceCollectionLimit($record, string $field): void
    {
        $limit = $record->getMediaCollection($field)?->collectionSizeLimit;
        if (! $limit) { return; }
        foreach ($record->media()->where('collection_name', $field)->orderByDesc('id')->get()->skip($limit) as $media) {
            $this->retire($media);
        }
    }
}
