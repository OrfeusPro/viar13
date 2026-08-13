<?php

namespace App\Models;

use App;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\GalleryHolst
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string $name
 * @property string $density
 * @property string $hint
 * @property float $price
 * @property int $hit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GalleryHolst newModelQuery()
 * @method static Builder|GalleryHolst newQuery()
 * @method static Builder|GalleryHolst query()
 * @method static Builder|GalleryHolst whereCreatedAt($value)
 * @method static Builder|GalleryHolst whereDensity($value)
 * @method static Builder|GalleryHolst whereHint($value)
 * @method static Builder|GalleryHolst whereHit($value)
 * @method static Builder|GalleryHolst whereId($value)
 * @method static Builder|GalleryHolst whereName($value)
 * @method static Builder|GalleryHolst wherePrice($value)
 * @method static Builder|GalleryHolst whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GalleryHolst whereUpdatedAt($value)
 * @method static Builder|GalleryHolst withTranslation($locale = null, $fallback = true)
 * @method static Builder|GalleryHolst withTranslations($locales = null, $fallback = true)
 */
class GalleryHolst extends Model
{
    use Translatable;

    protected $translatable = ['name', 'density', 'hint'];

    public static function getHolstNameById($id)
    {
        return GalleryHolst::where('id', $id)->get()->translate(App::getLocale(), 'ru')->pluck('name')->first();
    }
}
