<?php

namespace App;

use App;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use TCG\Voyager\Traits\Translatable;
use Throwable;

/**
 * App\Models\GalleryCategory
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property int|null $id_type
 * @property string $name
 * @property string|null $url
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GalleryCategory newModelQuery()
 * @method static Builder|GalleryCategory newQuery()
 * @method static Builder|GalleryCategory query()
 * @method static Builder|GalleryCategory whereActive($value)
 * @method static Builder|GalleryCategory whereCreatedAt($value)
 * @method static Builder|GalleryCategory whereId($value)
 * @method static Builder|GalleryCategory whereIdType($value)
 * @method static Builder|GalleryCategory whereName($value)
 * @method static Builder|GalleryCategory whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GalleryCategory whereUpdatedAt($value)
 * @method static Builder|GalleryCategory whereUrl($value)
 * @method static Builder|GalleryCategory withTranslation($locale = null, $fallback = true)
 * @method static Builder|GalleryCategory withTranslations($locales = null, $fallback = true)
 */
class GalleryCategory extends Model
{
    use Translatable;
    use HasSlug;

    protected $fillable = [

        'id', 'name', 'id_type', 'active', 'url',

    ];
    protected $translatable = ['name'];

    public static function getNameById($id)
    {
        return GalleryCategory::withTranslation(App::getLocale(), false)->where('id', $id)->get()[0];
    }

    public static function getUrlById($page_url, $cat_url)
    {
        return $page_url . '/' . $cat_url;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('url')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getAll($type)
    {
        $gp = new GalleryType();

        try {
            $id_type = $gp::where('url', $type)->first()->id;
        } catch (Throwable $th) {
            return abort(404);
        }

        try {
            $gal_cat = GalleryCategory::withTranslation(App::getLocale(), false)->where('id_type', $id_type)->get();
        } catch (Throwable $th) {
            return abort(404);
        }

        return $gal_cat;
    }

    public function getBy($category)
    {
        try {
            $gal_cat = GalleryCategory::where('url', $category)->first();
        } catch (Throwable $th) {
            return abort(404);
        }

        return $gal_cat;
    }

    public function getTags($category)
    {
        $tag = new GalleryTagRoom();

        return $tag->getTagForCategory($category);
    }

    public function getColors($category)
    {
        $tag = new GalleryTagColor();

        return $tag->getTagForCategory($category);
    }

    public function getItems($type, $category, $filtr)
    {
        $items = new GalleryItem();

        return $items->getItems($type, $category, $filtr);
    }
}
