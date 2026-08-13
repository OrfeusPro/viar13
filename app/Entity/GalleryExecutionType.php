<?php

namespace App\Entity;

use BenSampo\Enum\Enum;

class GalleryExecutionType extends Enum
{
    const OIL_TYPE = 1;
    const PRINT_TYPE = 2;

    public static function getName($type)
    {
        switch ($type) {
            case self::OIL_TYPE:
                $string = __('modular_pictures.index46');
                break;
            case self::PRINT_TYPE:
                $string = __('modular_pictures.index45');
                break;
            default:
                $string = 'No such id!';
                break;
        }

        return $string;
    }
}
