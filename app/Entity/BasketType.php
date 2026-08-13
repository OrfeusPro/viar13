<?php

namespace App\Entity;

class BasketType
{
    const CANVAS_TYPE = 1;
    const MODULAR_PICTURES_TYPE = 2;
    const COLLAGE_TYPE = 3;
    const COLLAGE_INTER = 4;
    const GIFT_CARD = 5;

    public static function getName($type)
    {
        switch ($type) {
            case self::CANVAS_TYPE:
                $string = 'Canvas';
                break;
            case self::MODULAR_PICTURES_TYPE:
                $string = 'Modular pictures';
                break;
            case self::COLLAGE_TYPE:
                $string = 'Collage constructor';
                break;
            case self::GIFT_CARD:
                $string = 'Gift card';
                break;
            default:
                $string = 'No such id!';
                break;
        }

        return $string;
    }
}
