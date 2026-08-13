<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\CollagePopularItem
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $name
 * @property string|null $image
 * @property string|null $param
 * @property string|null $desc
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $price_from
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CollagePopularItem newModelQuery()
 * @method static Builder|CollagePopularItem newQuery()
 * @method static Builder|CollagePopularItem query()
 * @method static Builder|CollagePopularItem whereCreatedAt($value)
 * @method static Builder|CollagePopularItem whereDesc($value)
 * @method static Builder|CollagePopularItem whereId($value)
 * @method static Builder|CollagePopularItem whereImage($value)
 * @method static Builder|CollagePopularItem whereName($value)
 * @method static Builder|CollagePopularItem whereParam($value)
 * @method static Builder|CollagePopularItem wherePriceFrom($value)
 * @method static Builder|CollagePopularItem whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CollagePopularItem whereUpdatedAt($value)
 * @method static Builder|CollagePopularItem withTranslation($locale = null, $fallback = true)
 * @method static Builder|CollagePopularItem withTranslations($locales = null, $fallback = true)
 */
class CollagePopularItem extends Model
{
    use Translatable;

    protected $table = 'collage_popular_items';

    protected $translatable = ['name', 'desc'];
}
