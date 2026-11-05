<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Tests\TestCase;

class ExistingMediaPathTest extends TestCase
{
    public function test_default_generator_finds_existing_legacy_files_for_other_models(): void
    {
        Storage::fake('public');
        $media = $this->media();
        Storage::disk('public')->put('users/media/123/example.jpg', 'legacy');
        Storage::disk('public')->put('123/example.jpg', 'standard');
        $generator = PathGeneratorFactory::create($media);
        $this->assertSame('users/media/123/', $generator->getPath($media));
        $this->assertStringEndsWith('/storage/users/media/123/example.jpg', $media->getUrl());
        $this->assertSame('123/conversions/', $generator->getPathForConversions($media));
        $this->assertSame('123/responsive-images/', $generator->getPathForResponsiveImages($media));
        $this->assertSame('standard', Storage::disk('public')->get('123/example.jpg'));
    }

    public function test_empty_old_directory_does_not_capture_new_uploads(): void
    {
        Storage::fake('public');
        Storage::disk('public')->makeDirectory('users/media/123');
        $media = $this->media();
        $generator = PathGeneratorFactory::create($media);
        $this->assertSame('123/', $generator->getPath($media));
        Storage::disk('public')->put('123/example.jpg', 'new');
        $this->assertSame('123/', $generator->getPath($media));
        config(['media-library.prefix'=>'library']);
        Storage::disk('public')->put('library/123/example.jpg', 'prefixed');
        $this->assertSame('library/123/', $generator->getPath($media));
    }

    public function test_morph_alias_resolves_to_original_model_directory(): void
    {
        Storage::fake('public');
        $before = Relation::morphMap();
        try {
            Relation::morphMap(['account'=>User::class]);
            $media = $this->media();
            $media->model_type = 'account';
            Storage::disk('public')->put('users/media/123/example.jpg', 'legacy');
            $this->assertSame('users/media/123/', PathGeneratorFactory::create($media)->getPath($media));
        } finally {
            Relation::morphMap($before, false);
        }
    }

    private function media(): Media
    {
        $media = new Media;
        $media->forceFill(['id'=>123,'model_type'=>User::class,'model_id'=>1,'disk'=>'public',
            'conversions_disk'=>'public','file_name'=>'example.jpg']);
        return $media;
    }
}
