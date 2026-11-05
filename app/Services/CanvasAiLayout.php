<?php
namespace App\Services;

class CanvasAiLayout
{
    const BORDER_CM = 3.0;

    public static function geometry($widthCm, $heightCm)
    {
        $scale = 1024 / max($widthCm + 2*self::BORDER_CM, $heightCm + 2*self::BORDER_CM);
        $w = (int)floor($widthCm * $scale);
        $h = (int)floor($heightCm * $scale);
        $border = (int)floor(self::BORDER_CM * $scale);
        return ['w'=>$w,'h'=>$h,'border'=>$border,'x'=>(int)floor((1024-$w)/2),'y'=>(int)floor((1024-$h)/2)];
    }

    public static function prompt(array $g)
    {
        $x=$g['x']; $y=$g['y']; $w=$g['w']; $h=$g['h']; $b=$g['border'];
        return "Extend the SAME flat photograph or illustration beyond its existing crop. This is masked OUTPAINTING, not a new composition. "
            . "The immutable original occupies x={$x}, y={$y}, width={$w}, height={$h} pixels. Generate ONLY the transparent ring, {$b} pixels on EACH of the four sides, including corners, equivalent to 3 cm per side in print. "
            . "Keep the original at EXACTLY the same pixel position and scale. Do not shrink, enlarge, move, recrop, redraw or insert a smaller copy of the original. The centre will be restored pixel-for-pixel after your output; any changed alignment would create a visible seam. "
            . "Imagine the camera originally captured a slightly wider view. Continue the actual scene naturally across each boundary: extend ceiling and floor lines along their existing perspective, continue walls, foliage, sky, fabric and objects where they cross the crop. Match local colour, exposure, lighting, texture, brushwork, focus and grain at every seam. Treat the four corners as coherent continuations of adjacent sides. "
            . "DO NOT mirror, reflect, flip, tile, repeat, stretch or smear edge pixels. Do not duplicate faces, people, limbs, furniture, text or objects. Do not make a border, inset image, frame, mat, shadow, 3D canvas, product mockup or picture-within-picture. Existing walls and shadows belonging to the scene must continue naturally; do not add a new display background. "
            . "Preserve every protected pixel. Fill the entire editable ring with plausible new scene content, without transparent gaps, solid-colour bands, seams or decorative framing. Return one continuous flat image at the supplied dimensions.";
    }

    public static function finish($generated, $originalCanvas, array $g)
    {
        // The model cannot alter any pixels of the source front: restore them exactly.
        imagealphablending($generated, false);
        imagecopy($generated,$originalCanvas,$g['x'],$g['y'],$g['x'],$g['y'],$g['w'],$g['h']);
        $b=$g['border'];$width=$g['w']+2*$b;$height=$g['h']+2*$b;
        $result=imagecrop($generated,['x'=>$g['x']-$b,'y'=>$g['y']-$b,'width'=>$width,'height'=>$height]);
        if(!$result)throw new \RuntimeException('Invalid crop');
        return [$result,['x'=>$b/$width,'y'=>$b/$height,'w'=>$g['w']/$width,'h'=>$g['h']/$height]];
    }
}
