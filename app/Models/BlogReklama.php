<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class BlogReklama extends Model
{
	use Translatable;
		
    protected $table = 'blog_reklama';
    protected $translatable = ['title', 'promo_text', 'size', 'btn_text'];
	
    public function retype()
    {
        return $this->belongsTo(BlogReklamaType::class, 'type_id', 'id');
    }

    public function scopeRightbanner($query)
    {
        return $query->where('type_id', 3);
    }
	
}
