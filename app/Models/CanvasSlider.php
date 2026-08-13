<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use TCG\Voyager\Traits\Translatable;

class CanvasSlider extends Model
{
	use Translatable;
	protected $table = 'canvas_slider';
    protected $fillable = [];
    protected $translatable = ['title','sub_title','text_gift','text1','text2','text3','text4','btn','size_text', 'size_title'];
}
