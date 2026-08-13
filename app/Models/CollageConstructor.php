<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\CollageConstructor
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $bread_title
 * @property string|null $top_title
 * @property string|null $choose_prod1
 * @property string|null $choose_fon2
 * @property string|null $choose_form3
 * @property string|null $choose_size4
 * @property string|null $choose_holst5
 * @property string|null $comm6
 * @property string|null $sizes_list_items
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $sizes_1
 * @property string|null $sizes_2
 * @property string|null $sizes_3
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CollageConstructor newModelQuery()
 * @method static Builder|CollageConstructor newQuery()
 * @method static Builder|CollageConstructor query()
 * @method static Builder|CollageConstructor whereBreadTitle($value)
 * @method static Builder|CollageConstructor whereChooseFon2($value)
 * @method static Builder|CollageConstructor whereChooseForm3($value)
 * @method static Builder|CollageConstructor whereChooseHolst5($value)
 * @method static Builder|CollageConstructor whereChooseProd1($value)
 * @method static Builder|CollageConstructor whereChooseSize4($value)
 * @method static Builder|CollageConstructor whereComm6($value)
 * @method static Builder|CollageConstructor whereCreatedAt($value)
 * @method static Builder|CollageConstructor whereId($value)
 * @method static Builder|CollageConstructor whereMetaTitle($value)
 * @method static Builder|CollageConstructor whereSizes1($value)
 * @method static Builder|CollageConstructor whereSizes2($value)
 * @method static Builder|CollageConstructor whereSizes3($value)
 * @method static Builder|CollageConstructor whereSizesListItems($value)
 * @method static Builder|CollageConstructor whereTopTitle($value)
 * @method static Builder|CollageConstructor whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CollageConstructor whereUpdatedAt($value)
 * @method static Builder|CollageConstructor withTranslation($locale = null, $fallback = true)
 * @method static Builder|CollageConstructor withTranslations($locales = null, $fallback = true)
 */
class CollageConstructor extends Model
{
    use Translatable;

    protected $table = 'collage_constructor';

    protected $fillable = [];

    protected $translatable = [
        'meta_title', 'bread_title', 'top_title', 'choose_prod1', 'choose_fon2', 'choose_form3', 'choose_size4',
        'choose_holst5', 'comm6'
    ];
}
