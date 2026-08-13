<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class AMailTopSale extends Model
{
    use Translatable;
    protected $table = 'a_mail_top_sale';
	protected $translatable = ['title'];
}
