<?php

namespace App\Models;

use App;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\GalleryType
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string $name
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $url
 * @property float|null $price_var
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GalleryType newModelQuery()
 * @method static Builder|GalleryType newQuery()
 * @method static Builder|GalleryType query()
 * @method static Builder|GalleryType whereActive($value)
 * @method static Builder|GalleryType whereCreatedAt($value)
 * @method static Builder|GalleryType whereId($value)
 * @method static Builder|GalleryType whereName($value)
 * @method static Builder|GalleryType wherePriceVar($value)
 * @method static Builder|GalleryType whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GalleryType whereUpdatedAt($value)
 * @method static Builder|GalleryType whereUrl($value)
 * @method static Builder|GalleryType withTranslation($locale = null, $fallback = true)
 * @method static Builder|GalleryType withTranslations($locales = null, $fallback = true)
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
        $GalleryType = GalleryType::where('url', $url)->get()->translate(App::getLocale(), 'ru');
        if(isset($GalleryType[0]))
        {
            $GalleryType = $GalleryType[0];
        }
        else
        {
            $GalleryType = null;
        }
        //dd($gallery);
        return $GalleryType;
    }

    public function items()
    {
        return $this->hasMany(GalleryItem::class, 'id_type', 'id');
    }
}
