<?php

namespace App\Repositories;

use App\Models\GalleryBox;

class GalleryBoxRepository
{
    static public function getAll()
    {
        return GalleryBox::with('translations')
            ->get();
    }
}
