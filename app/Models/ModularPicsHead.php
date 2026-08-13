<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\ModularPicsHead
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $meta_desc
 * @property string|null $right1_title
 * @property string|null $right2_title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $price
 * @property string|null $pop_items_title
 * @property string|null $pop_items_order_text
 * @property string|null $our_works
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|ModularPicsHead newModelQuery()
 * @method static Builder|ModularPicsHead newQuery()
 * @method static Builder|ModularPicsHead query()
 * @method static Builder|ModularPicsHead whereCreatedAt($value)
 * @method static Builder|ModularPicsHead whereId($value)
 * @method static Builder|ModularPicsHead whereMetaDesc($value)
 * @method static Builder|ModularPicsHead whereMetaTitle($value)
 * @method static Builder|ModularPicsHead whereOurWorks($value)
 * @method static Builder|ModularPicsHead wherePopItemsOrderText($value)
 * @method static Builder|ModularPicsHead wherePopItemsTitle($value)
 * @method static Builder|ModularPicsHead wherePrice($value)
 * @method static Builder|ModularPicsHead whereRight1Title($value)
 * @method static Builder|ModularPicsHead whereRight2Title($value)
 * @method static Builder|ModularPicsHead whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|ModularPicsHead whereUpdatedAt($value)
 * @method static Builder|ModularPicsHead withTranslation($locale = null, $fallback = true)
 * @method static Builder|ModularPicsHead withTranslations($locales = null, $fallback = true)
 */
class ModularPicsHead extends Model
{
    use Translatable;

    protected $table = 'modular_pics_head';

    protected $fillable = [];

    protected $translatable = [
        'meta_title', 'meta_desc', 'right1_title', 'right2_title', 'pop_items_title', 'pop_items_order_text', 'seo_city_title', 'seo_city_desc',
    ];
}
