<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoCity extends Model
{
    protected $table = 'seo_city';

    protected $fillable = ['local', 'city'];
}
