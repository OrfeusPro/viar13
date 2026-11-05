<?php

namespace Tests\Unit;

use App\Filament\Bread\BreadImageCrop;
use PHPUnit\Framework\TestCase;

class MediaCropExifTest extends TestCase
{
    public function test_media_crop_preserves_voyager_raw_pixel_contract_for_all_exif_orientations(): void
    {
        $image = imagecreatetruecolor(40, 30);
        for ($y = 0; $y < 30; $y++) {
            for ($x = 0; $x < 40; $x++) {
                imagesetpixel($image, $x, $y, ($x * 6 << 16) | ($y * 8 << 8) | (($x + $y) * 3));
            }
        }
        ob_start();
        imagejpeg($image, null, 100);
        $jpeg = ob_get_clean();
        imagedestroy($image);
        $processor = new BreadImageCrop;
        $expected = $processor->crop($jpeg, 3, 4, 12, 9, 90, true, true)['bytes'];
        for ($orientation = 1; $orientation <= 8; $orientation++) {
            $exif = "Exif\0\0II".pack('vVv', 42, 8, 1).pack('vvV', 0x0112, 3, 1)
                .pack('v', $orientation)."\0\0".pack('V', 0);
            $fixture = substr($jpeg, 0, 2)."\xff\xe1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
            $actual = $processor->crop($fixture, 3, 4, 12, 9, 90, true, true)['bytes'];
            $this->assertSame($expected, $actual, 'Orientation '.$orientation.' must not rotate the server crop.');
            $info = getimagesizefromstring($actual);
            $this->assertSame([12, 9, 'image/jpeg'], [$info[0], $info[1], $info['mime']]);
        }
    }
}
