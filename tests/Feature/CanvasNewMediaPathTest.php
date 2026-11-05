<?php

namespace Tests\Feature;

use App\Models\CanvasNew;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Tests\TestCase;

class CanvasNewMediaPathTest extends TestCase
{
    public function test_legacy_canvas_media_resolves_original_path_and_keeps_conversion_paths(): void
    {
        Storage::fake('public');
        $media = $this->media();
        $path = 'canvasnews/media/262/example.jpg';
        Storage::disk('public')->put($path, 'Legacy original');
        $generator = PathGeneratorFactory::create($media);
        $this->assertSame('canvasnews/media/262/', $generator->getPath($media));
        $this->assertStringEndsWith('/storage/'.$path, $media->getUrl());
        $this->assertSame(Storage::disk('public')->path($path), $media->getPath());
        $this->assertSame('262/conversions/', $generator->getPathForConversions($media));
        $this->assertSame('262/responsive-images/', $generator->getPathForResponsiveImages($media));
        $this->assertSame('Legacy original', Storage::disk('public')->get($path));
    }

    public function test_standard_uploads_still_work_and_legacy_wins_when_both_exist(): void
    {
        Storage::fake('public');
        $media = $this->media();
        Storage::disk('public')->put('262/example.jpg', 'New original');
        $this->assertStringEndsWith('/storage/262/example.jpg', $media->getUrl());
        Storage::disk('public')->put('canvasnews/media/262/example.jpg', 'Legacy original');
        $this->assertStringEndsWith('/storage/canvasnews/media/262/example.jpg', $media->getUrl());
        $this->assertSame('New original', Storage::disk('public')->get('262/example.jpg'));
    }

    public function test_missing_local_canvas_file_uses_legacy_path_and_other_models_keep_default(): void
    {
        Storage::fake('public');
        $media = $this->media();
        $this->assertStringEndsWith('/storage/canvasnews/media/262/example.jpg', $media->getUrl());
        $media->model_type = \App\Models\User::class;
        $this->assertSame('262/', PathGeneratorFactory::create($media)->getPath($media));
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    private function media(): Media
    {
        $media = new Media;
        $media->forceFill(['id' => 262, 'model_type' => CanvasNew::class, 'model_id' => 15,
            'disk' => 'public', 'conversions_disk' => 'public', 'file_name' => 'example.jpg']);

        return $media;
    }
}
