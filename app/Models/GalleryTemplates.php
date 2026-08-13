<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GalleryTemplates extends Model
{

    protected $table = 'gallery_templates';
    protected $fillable = [
        'id', 'sizeprice1', 'sizeprice2', 'sizeprice3','type'
    ];

    public function galleryItems()
    {
        return $this->hasMany(GalleryItem::class);
    }

}
