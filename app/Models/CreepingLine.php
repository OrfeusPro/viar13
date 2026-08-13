<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class CreepingLine extends Model
{
	use Translatable;
	protected $table = 'creeping_line';
    protected $translatable = ['text'];
}
