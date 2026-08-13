<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;


class ACollageOrderScreen extends Model
{
    use Resizable;
	
    protected $table = 'a_collage_order_screen';
}
