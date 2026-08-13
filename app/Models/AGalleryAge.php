<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class AGalleryAge extends Model
{
	use Translatable;
    protected $table = 'a_gallery_age';
	protected $translatable = ['name', 'meta_title', 'meta_description'];
}
