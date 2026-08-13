<?php

namespace App\Repositories;

use App\Models\GalleryHolst;

class GalleryHolstRepository
{
    public function getAll()
    {
        return GalleryHolst::withTranslation(\App::getLocale())->where('name', '!=', '')->get();
    }
}
