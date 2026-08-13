<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use TCG\Voyager\Traits\Translatable;

class BlogCategory extends Model
{
    use Translatable;
    use HasSlug;

    protected $table = 'blog_categories';
    protected $fillable = [];
    protected $translatable = ['meta_title', 'meta_desc', 'title', 'seo'];

    public function getSlugOptions()
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function posts()
    {
        return $this->belongsToMany(BlogPost::class);
    }
}
