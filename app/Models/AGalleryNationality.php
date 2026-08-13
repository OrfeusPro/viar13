<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class AGalleryNationality extends Model
{
	use Translatable;
    protected $table = 'a_gallery_nationality';
	protected $translatable = ['name', 'meta_title', 'meta_description'];
}
