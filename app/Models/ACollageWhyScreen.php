<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use TCG\Voyager\Traits\Translatable;


class ACollageWhyScreen extends Model
{
    use Resizable;
    use Translatable;
	
    protected $table = 'a_collage_why_screen';
	protected $translatable = ['title', 'text'];
}

