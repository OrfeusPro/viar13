<?php


namespace App;


use App;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;


/**
 * @mixin Eloquent
 * @mixin Builder
 */
class GalleryType extends Model

{

    use Translatable;


    protected $fillable = [

        'id', 'name', 'url', 'price_var',

    ];


    protected $translatable = ['name'];


    public function getType($url)
    {

        return GalleryType::where('url', $url)->get()->translate(App::getLocale(), 'ru')[0];

    }

}

