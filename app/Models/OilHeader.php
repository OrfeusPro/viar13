<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\OilHeader
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $meta_desc
 * @property string|null $left_title
 * @property string|null $left_subtitle
 * @property string|null $right_title
 * @property string|null $t1_title
 * @property string|null $t2_title
 * @property string|null $t3_title
 * @property string|null $t4_title
 * @property string|null $t5_title
 * @property string|null $t6_title
 * @property string|null $quest_title
 * @property string|null $order_title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $what_is
 * @property string|null $port_diff_hart_title
 * @property string|null $port_diff_hart_text
 * @property string|null $port_prev_title
 * @property string|null $custom_users_prices
 * @property string|null $sizes_cals
 * @property string|null $oil_sizes_calc_form1
 * @property string|null $oil_sizes_calc_form2
 * @property string|null $oil_sizes_calc_form3
 * @property string|null $our_works
 * @property string|null $images
 * @property string|null $zl_title
 * @property string|null $zl_title_2
 * @property string|null $zl_title_3
 * @property string|null $zl_title_4
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|OilHeader newModelQuery()
 * @method static Builder|OilHeader newQuery()
 * @method static Builder|OilHeader query()
 * @method static Builder|OilHeader whereCreatedAt($value)
 * @method static Builder|OilHeader whereCustomUsersPrices($value)
 * @method static Builder|OilHeader whereId($value)
 * @method static Builder|OilHeader whereImages($value)
 * @method static Builder|OilHeader whereLeftSubtitle($value)
 * @method static Builder|OilHeader whereLeftTitle($value)
 * @method static Builder|OilHeader whereMetaDesc($value)
 * @method static Builder|OilHeader whereMetaTitle($value)
 * @method static Builder|OilHeader whereOilSizesCalcForm1($value)
 * @method static Builder|OilHeader whereOilSizesCalcForm2($value)
 * @method static Builder|OilHeader whereOilSizesCalcForm3($value)
 * @method static Builder|OilHeader whereOrderTitle($value)
 * @method static Builder|OilHeader whereOurWorks($value)
 * @method static Builder|OilHeader wherePortDiffHartText($value)
 * @method static Builder|OilHeader wherePortDiffHartTitle($value)
 * @method static Builder|OilHeader wherePortPrevTitle($value)
 * @method static Builder|OilHeader whereQuestTitle($value)
 * @method static Builder|OilHeader whereRightTitle($value)
 * @method static Builder|OilHeader whereSizesCals($value)
 * @method static Builder|OilHeader whereT1Title($value)
 * @method static Builder|OilHeader whereT2Title($value)
 * @method static Builder|OilHeader whereT3Title($value)
 * @method static Builder|OilHeader whereT4Title($value)
 * @method static Builder|OilHeader whereT5Title($value)
 * @method static Builder|OilHeader whereT6Title($value)
 * @method static Builder|OilHeader whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|OilHeader whereUpdatedAt($value)
 * @method static Builder|OilHeader whereWhatIs($value)
 * @method static Builder|OilHeader whereZlTitle($value)
 * @method static Builder|OilHeader whereZlTitle2($value)
 * @method static Builder|OilHeader whereZlTitle3($value)
 * @method static Builder|OilHeader whereZlTitle4($value)
 * @method static Builder|OilHeader withTranslation($locale = null, $fallback = true)
 * @method static Builder|OilHeader withTranslations($locales = null, $fallback = true)
 */
class OilHeader extends Model
{
    use Translatable;

    protected $table = 'oil_header';

    protected $fillable = [];

    protected $translatable = [
        'meta_title', 'meta_desc', 'left_title', 'left_subtitle', 'right_title', 't1_title',

        't2_title', 't3_title', 't4_title', 't5_title', 't6_title', 'quest_title', 'order_title', 'what_is',

        'port_diff_hart_title', 'port_diff_hart_text', 'port_prev_title', 'zl_title', 'zl_title_2', 'zl_title_3',
        'zl_title_4',
    ];

    public static function getOilPromoImage()
    {
        return OilHeader::pluck('images')->first();
    }
}
