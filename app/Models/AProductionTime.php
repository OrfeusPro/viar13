<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;


class AProductionTime extends Model
{
    use Translatable;
    protected $table = 'a_production_time';
    protected $translatable = ['standart_text', 'express_text'];

}
