<?php
namespace App\Services;

class CanvasAiLayout
{
    const BORDER_CM = 3.5;

    public static function geometry($widthCm, $heightCm)
    {
        $scale = 1024 / max($widthCm + 7, $heightCm + 7);
        $w = (int)floor($widthCm * $scale);
        $h = (int)floor($heightCm * $scale);
        $border = (int)floor(self::BORDER_CM * $scale);
        return ['w'=>$w,'h'=>$h,'border'=>$border,'x'=>(int)floor((1024-$w)/2),'y'=>(int)floor((1024-$h)/2)];
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
