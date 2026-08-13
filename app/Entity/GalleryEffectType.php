<?php

namespace App\Entity;

use BenSampo\Enum\Enum;

class GalleryEffectType extends Enum
{
    const ORIGINAL_TYPE = 1;
    const BLACK_ANT_WHITE_TYPE = 2;
    const SEPIA_TYPE = 3;

    public static function getName($type)
    {
        switch ($type) {
            case self::ORIGINAL_TYPE:
                $string = __('modular_pictures.index51');
                break;
            case self::BLACK_ANT_WHITE_TYPE:
                $string = __('modular_pictures.index52');
                break;
            case self::SEPIA_TYPE:
                $string = __('modular_pictures.index53');
                break;
            default:
                $string = 'No such id!';
                break;
        }

        return $string;
    }
}
