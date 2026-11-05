<?php

namespace Tests\Feature\Admin;

use App\Filament\Bread\BreadImageUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MediaImageProcessingTest extends TestCase
{
    private function stripedPng(): string
    {
        $image = imagecreatetruecolor(60, 30);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 0, 0));
        imagefilledrectangle($image, 30, 0, 59, 29, imagecolorallocate($image, 0, 0, 255));
        ob_start(); imagepng($image); $bytes = ob_get_clean(); imagedestroy($image);
        return $bytes;
    }

    private function pixel(string $bytes, int $x, int $y): int
    {
        $image = imagecreatefromstring($bytes);
        try { return imagecolorat($image, $x, $y); }
        finally { imagedestroy($image); }
    }

    public function test_media_thumbnail_modes_positions_and_upsize(): void
    {
        $variants = app(BreadImageUpload::class)->mediaVariants(UploadedFile::fake()->createWithContent('original.png', $this->stripedPng()), 'uploads', [
            'thumbnails' => [
                ['name' => 'left', 'width' => 15, 'position' => 'left'],
                ['name' => 'right', 'type' => 'fit', 'width' => 15, 'height' => 15, 'position' => 'right'],
                ['name' => 'cut', 'type' => 'crop', 'width' => 12, 'height' => 13, 'x' => 30, 'y' => 1],
                ['name' => 'center', 'type' => 'crop', 'width' => 10, 'height' => 10],
                ['name' => 'small', 'type' => 'resize', 'width' => 20],
                ['name' => 'height', 'type' => 'resize', 'height' => 10],
                ['name' => 'bounded', 'type' => 'resize', 'width' => 120, 'height' => 120, 'upsize' => false],
            ],
        ]);
        foreach (['-left' => [15, 15], '-right' => [15, 15], '-cut' => [12, 13], '-center' => [10, 10],
            '-small' => [20, 10], '-height' => [20, 10], '-bounded' => [60, 30], '' => [60, 30]] as $suffix => $size) {
            $info = getimagesizefromstring($variants[$suffix]);
            $this->assertSame($size, [$info[0], $info[1]], $suffix);
        }
        $this->assertSame(0xff0000, $this->pixel($variants['-left'], 7, 7));
        $this->assertSame(0x0000ff, $this->pixel($variants['-right'], 7, 7));
        $this->assertSame(0x0000ff, $this->pixel($variants['-cut'], 0, 0));
        $this->assertSame(0xff0000, $this->pixel($variants['-center'], 0, 0));
        $this->assertSame(0x0000ff, $this->pixel($variants['-center'], 9, 0));
    }

    public function test_watermark_applies_to_main_and_opted_in_thumbnails(): void
    {
        Storage::fake('public');
        $mark = imagecreatetruecolor(4, 4);
        imagefill($mark, 0, 0, imagecolorallocate($mark, 0, 255, 0));
        ob_start(); imagepng($mark); $bytes = ob_get_clean(); imagedestroy($mark);
        Storage::disk('public')->put('watermark.png', $bytes);
        $variants = app(BreadImageUpload::class)->mediaVariants(UploadedFile::fake()->createWithContent('original.png', $this->stripedPng()), 'uploads', [
            'watermark' => ['source' => 'watermark.png', 'size' => 20, 'position' => 'bottom-right', 'x' => 2, 'y' => 1],
            'thumbnails' => [
                ['name' => 'plain', 'type' => 'fit', 'width' => 20, 'position' => 'left'],
                ['name' => 'marked', 'type' => 'fit', 'width' => 20, 'position' => 'left', 'watermark' => true],
            ],
        ]);
        $this->assertSame(0x00ff00, $this->pixel($variants[''], 46, 17));
        $this->assertSame(0x0000ff, $this->pixel($variants[''], 59, 29));
        $this->assertSame(0xff0000, $this->pixel($variants[''], 0, 0));
        $this->assertSame(0xff0000, $this->pixel($variants['-plain'], 14, 15));
        $this->assertSame(0x00ff00, $this->pixel($variants['-marked'], 14, 15));
        $this->assertSame($bytes, Storage::disk('public')->get('watermark.png'));
    }

    public function test_media_details_do_not_modify_gif_or_documents(): void
    {
        $details = ['thumbnails' => [['name' => 'small', 'width' => 20]], 'watermark' => ['source' => 'missing.png']];
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        foreach (['animation.gif' => $gif, 'document.txt' => 'Document'] as $name => $bytes) {
            $this->assertSame(['' => $bytes], app(BreadImageUpload::class)->mediaVariants(UploadedFile::fake()->createWithContent($name, $bytes), 'uploads', $details));
        }
    }

    public function test_watermark_fractional_width_matches_original_height_rounding(): void
    {
        Storage::fake('public');
        $mark = imagecreatetruecolor(7, 5);
        imagefill($mark, 0, 0, imagecolorallocate($mark, 0, 255, 0));
        ob_start(); imagepng($mark); $bytes = ob_get_clean(); imagedestroy($mark);
        Storage::disk('public')->put('mark.png', $bytes);
        $variants = app(BreadImageUpload::class)->mediaVariants(UploadedFile::fake()->image('main.png', 61, 31), 'uploads',
            ['watermark' => ['source' => 'mark.png', 'size' => 15]]);
        $this->assertSame(0x00ff00, $this->pixel($variants[''], 8, 6));
        $this->assertNotSame(0x00ff00, $this->pixel($variants[''], 9, 6));
        $this->assertNotSame(0x00ff00, $this->pixel($variants[''], 8, 7));
    }

    public function test_invalid_thumbnail_configuration_fails_before_storage_writes(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->createWithContent('original.png', $this->stripedPng());
        foreach ([['name' => '../outside', 'width' => 20], ['name' => 'huge', 'width' => 10000],
            ['name' => 'bad', 'width' => 20, 'position' => 'unknown'], ['name' => 'bad', 'type' => 'crop', 'width' => 20]] as $thumbnail) {
            try {
                app(BreadImageUpload::class)->mediaVariants($file, 'uploads', ['thumbnails' => [$thumbnail]]);
                $this->fail('Invalid thumbnail was accepted.');
            } catch (ValidationException $error) { $this->assertArrayHasKey('uploads', $error->errors()); }
        }
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
