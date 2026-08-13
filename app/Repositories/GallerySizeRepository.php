<?php

namespace App\Repositories;

use App\Models\GallerySize;

class GallerySizeRepository
{
    public function getAll()
    {
        return GallerySize::all();
    }
}
