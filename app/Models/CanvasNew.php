<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use TCG\Voyager\Traits\Translatable;


class CanvasNew extends Model implements HasMedia
{
    protected $table = 'canvas_new';
    use InteractsWithMedia;
    use Translatable;

    protected $translatable = [
        'seo',
        'description',
    ];
}
