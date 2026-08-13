<?php

namespace App\Entity;

use BenSampo\Enum\Enum;

class GalleryFormType extends Enum
{
    const BIG_TYPE = 1;
    const MEDIUM_TYPE = 2;
    const SMALL_TYPE = 3;

    public static function getName($type)
    {
        switch ($type) {
            case self::BIG_TYPE:
                $string = __('modular_pictures.index48');
                break;
            case self::MEDIUM_TYPE:
                $string = __('modular_pictures.index49');
                break;
            case self::SMALL_TYPE:
                $string = __('modular_pictures.index50');
                break;
            default:
                $string = 'No such id!';
                break;
        }

        return $string;
    }
}
