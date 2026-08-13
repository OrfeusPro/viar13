<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class AGalleryGenre extends Model
{
	use Translatable;
    protected $table = 'a_gallery_genre';
	protected $translatable = ['name', 'meta_title', 'meta_description'];
}
