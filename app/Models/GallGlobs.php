<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\GallGlobs
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $pop_modc_title
 * @property string|null $size_title
 * @property string|null $price_text
 * @property string|null $price_from_text
 * @property string|null $order_text
 * @property string|null $rec_modc_title
 * @property string|null $all_cat_arts_title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $pop_photoc_title
 * @property string|null $rec_photoc_title
 * @property string|null $pop_reprc_title
 * @property string|null $rec_reprc_title
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GallGlobs newModelQuery()
 * @method static Builder|GallGlobs newQuery()
 * @method static Builder|GallGlobs query()
 * @method static Builder|GallGlobs whereAllCatArtsTitle($value)
 * @method static Builder|GallGlobs whereCreatedAt($value)
 * @method static Builder|GallGlobs whereId($value)
 * @method static Builder|GallGlobs whereOrderText($value)
 * @method static Builder|GallGlobs wherePopModcTitle($value)
 * @method static Builder|GallGlobs wherePopPhotocTitle($value)
 * @method static Builder|GallGlobs wherePopReprcTitle($value)
 * @method static Builder|GallGlobs wherePriceFromText($value)
 * @method static Builder|GallGlobs wherePriceText($value)
 * @method static Builder|GallGlobs whereRecModcTitle($value)
 * @method static Builder|GallGlobs whereRecPhotocTitle($value)
 * @method static Builder|GallGlobs whereRecReprcTitle($value)
 * @method static Builder|GallGlobs whereSizeTitle($value)
 * @method static Builder|GallGlobs whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GallGlobs whereUpdatedAt($value)
 * @method static Builder|GallGlobs withTranslation($locale = null, $fallback = true)
 * @method static Builder|GallGlobs withTranslations($locales = null, $fallback = true)
 */
class GallGlobs extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $translatable = [
        'pop_modc_title', 'size_title', 'price_text', 'price_from_text',

        'order_text', 'rec_modc_title', 'all_cat_arts_title', 'pop_photoc_title', 'rec_photoc_title',

        'pop_reprc_title', 'rec_reprc_title',
    ];
}
