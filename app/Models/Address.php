<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class Address extends Model
{
    use Translatable;
    protected $table = 'address';
	protected $translatable = ['title', 'type', "working_hours", "city", "gmail_url_embed", "subtitle"];
}
