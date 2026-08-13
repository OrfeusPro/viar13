<?php

namespace App\Repositories;

use App\Models\GalleryDecoration;

class GalleryDecorationRepository
{
    static public function getAll()
    {
        return GalleryDecoration::with('translations')
            ->get();
    }
}
