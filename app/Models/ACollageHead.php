<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class ACollageHead extends Model
{
    use Translatable;

	protected $table = 'a_collage_head';
	protected $translatable = ['name', 'seo', 'seo_city_title', 'seo_city_desc'];
}
