<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use TCG\Voyager\Traits\Translatable;

class ACollageStickerGroup extends Model
{
    use Translatable;
	protected $table = 'a_collage_sticker_group';
	
	protected $translatable = ['title'];
}
