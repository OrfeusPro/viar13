<?php

namespace App;

use App;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\GalleryBox
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string $name
 * @property string $hint
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GalleryBox newModelQuery()
 * @method static Builder|GalleryBox newQuery()
 * @method static Builder|GalleryBox query()
 * @method static Builder|GalleryBox whereCreatedAt($value)
 * @method static Builder|GalleryBox whereHint($value)
 * @method static Builder|GalleryBox whereId($value)
 * @method static Builder|GalleryBox whereName($value)
 * @method static Builder|GalleryBox wherePrice($value)
 * @method static Builder|GalleryBox whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GalleryBox whereUpdatedAt($value)
 * @method static Builder|GalleryBox withTranslation($locale = null, $fallback = true)
 * @method static Builder|GalleryBox withTranslations($locales = null, $fallback = true)
 */
class GalleryBox extends Model
{
    use Translatable;

    protected $fillable = [

        'id', 'name', 'hint',

    ];

    protected $translatable = ['name', 'hint'];

    public static function getNameById($id)
    {
        return GalleryBox::where('id', $id)->get()->translate(App::getLocale(), 'ru')[0];
    }
}
