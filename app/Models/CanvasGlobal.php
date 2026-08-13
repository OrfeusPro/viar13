<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\CanvasGlobal
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property float|null $price
 * @property string|null $def_int_title
 * @property string|null $rama_title
 * @property string|null $total_price_title
 * @property string|null $add_to_bask_title
 * @property string|null $timings_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $w_size
 * @property string|null $cm
 * @property string|null $cart_size
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CanvasGlobal newModelQuery()
 * @method static Builder|CanvasGlobal newQuery()
 * @method static Builder|CanvasGlobal query()
 * @method static Builder|CanvasGlobal whereAddToBaskTitle($value)
 * @method static Builder|CanvasGlobal whereCartSize($value)
 * @method static Builder|CanvasGlobal whereCm($value)
 * @method static Builder|CanvasGlobal whereCreatedAt($value)
 * @method static Builder|CanvasGlobal whereDefIntTitle($value)
 * @method static Builder|CanvasGlobal whereId($value)
 * @method static Builder|CanvasGlobal wherePrice($value)
 * @method static Builder|CanvasGlobal whereRamaTitle($value)
 * @method static Builder|CanvasGlobal whereTimingsText($value)
 * @method static Builder|CanvasGlobal whereTotalPriceTitle($value)
 * @method static Builder|CanvasGlobal whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CanvasGlobal whereUpdatedAt($value)
 * @method static Builder|CanvasGlobal whereWSize($value)
 * @method static Builder|CanvasGlobal withTranslation($locale = null, $fallback = true)
 * @method static Builder|CanvasGlobal withTranslations($locales = null, $fallback = true)
 */
class CanvasGlobal extends Model
{
    use Translatable;

    protected $table = 'canvas_global';

    protected $fillable = [];

    protected $translatable = [

        'price', 'def_int_title', 'rama_title', 'total_price_title', 'add_to_bask_title',

        'timings_text', 'w_size', 'cm', 'cart_size',

    ];
}
