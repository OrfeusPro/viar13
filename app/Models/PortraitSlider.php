<?php
namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use TCG\Voyager\Traits\Translatable;

class PortraitSlider extends Model
{
	use Translatable;
	protected $table = 'portrait_slider';
    protected $fillable = [];
    protected $translatable = ['title','sub_title','text_gift','text1','text2','text3','text4','btn','size_text', 'size_title'];
}
