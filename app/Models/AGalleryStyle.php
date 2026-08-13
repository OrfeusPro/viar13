<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class AGalleryStyle extends Model
{
	use Translatable;
    protected $table = 'a_gallery_style';	
	protected $translatable = ['name', 'meta_title', 'meta_description'];
}
