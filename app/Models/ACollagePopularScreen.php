<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use TCG\Voyager\Traits\Translatable;


class ACollagePopularScreen extends Model
{
    use Resizable;
    use Translatable;

    protected $table = 'a_collage_popular_screen';
	protected $translatable = ['title', 'price', 'btn_text'];
}
