<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\CanvasHeader
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $c_left1
 * @property string|null $c_left2
 * @property string|null $c_right_top
 * @property string|null $c_right1
 * @property string|null $c_right2
 * @property string|null $c_right3
 * @property string|null $c_right4
 * @property string|null $c_right5
 * @property string|null $c_right6
 * @property string|null $c_right1_quest_title
 * @property string|null $c_right_order_title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $meta_title
 * @property string|null $calc_sub_title
 * @property string|null $c_tab1_title
 * @property string|null $c_tab2_title
 * @property string|null $c_tab3_title
 * @property string|null $c_tab4_title
 * @property string|null $c_tab5_title
 * @property string|null $c_tab6_title
 * @property string|null $c_tab7_title
 * @property string|null $c_tab8_title
 * @property string|null $c_eff_origin
 * @property string|null $c_eff_cb
 * @property string|null $c_eff_sepia
 * @property string|null $calc_tab_1_main_title
 * @property string|null $calc_tab_2_main_title
 * @property string|null $tear_away_text
 * @property string|null $share_text
 * @property string|null $load_btn_text
 * @property string|null $person_count
 * @property string|null $choose_pack
 * @property string|null $our_works
 * @property string|null $sizes_30x40
 * @property string|null $sizes_38x38
 * @property string|null $sizes_40x30
 * @property string|null $sizes_60x30
 * @property string|null $meta_desc
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CanvasHeader newModelQuery()
 * @method static Builder|CanvasHeader newQuery()
 * @method static Builder|CanvasHeader query()
 * @method static Builder|CanvasHeader whereCEffCb($value)
 * @method static Builder|CanvasHeader whereCEffOrigin($value)
 * @method static Builder|CanvasHeader whereCEffSepia($value)
 * @method static Builder|CanvasHeader whereCLeft1($value)
 * @method static Builder|CanvasHeader whereCLeft2($value)
 * @method static Builder|CanvasHeader whereCRight1($value)
 * @method static Builder|CanvasHeader whereCRight1QuestTitle($value)
 * @method static Builder|CanvasHeader whereCRight2($value)
 * @method static Builder|CanvasHeader whereCRight3($value)
 * @method static Builder|CanvasHeader whereCRight4($value)
 * @method static Builder|CanvasHeader whereCRight5($value)
 * @method static Builder|CanvasHeader whereCRight6($value)
 * @method static Builder|CanvasHeader whereCRightOrderTitle($value)
 * @method static Builder|CanvasHeader whereCRightTop($value)
 * @method static Builder|CanvasHeader whereCTab1Title($value)
 * @method static Builder|CanvasHeader whereCTab2Title($value)
 * @method static Builder|CanvasHeader whereCTab3Title($value)
 * @method static Builder|CanvasHeader whereCTab4Title($value)
 * @method static Builder|CanvasHeader whereCTab5Title($value)
 * @method static Builder|CanvasHeader whereCTab6Title($value)
 * @method static Builder|CanvasHeader whereCTab7Title($value)
 * @method static Builder|CanvasHeader whereCTab8Title($value)
 * @method static Builder|CanvasHeader whereCalcSubTitle($value)
 * @method static Builder|CanvasHeader whereCalcTab1MainTitle($value)
 * @method static Builder|CanvasHeader whereCalcTab2MainTitle($value)
 * @method static Builder|CanvasHeader whereChoosePack($value)
 * @method static Builder|CanvasHeader whereCreatedAt($value)
 * @method static Builder|CanvasHeader whereId($value)
 * @method static Builder|CanvasHeader whereLoadBtnText($value)
 * @method static Builder|CanvasHeader whereMetaDesc($value)
 * @method static Builder|CanvasHeader whereMetaTitle($value)
 * @method static Builder|CanvasHeader whereOurWorks($value)
 * @method static Builder|CanvasHeader wherePersonCount($value)
 * @method static Builder|CanvasHeader whereShareText($value)
 * @method static Builder|CanvasHeader whereSizes30x40($value)
 * @method static Builder|CanvasHeader whereSizes38x38($value)
 * @method static Builder|CanvasHeader whereSizes40x30($value)
 * @method static Builder|CanvasHeader whereSizes60x30($value)
 * @method static Builder|CanvasHeader whereTearAwayText($value)
 * @method static Builder|CanvasHeader whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CanvasHeader whereUpdatedAt($value)
 * @method static Builder|CanvasHeader withTranslation($locale = null, $fallback = true)
 * @method static Builder|CanvasHeader withTranslations($locales = null, $fallback = true)
 */
class CanvasHeader extends Model
{
    use Translatable;

    protected $table = 'canvas_header';

    protected $fillable = [];

    protected $translatable = [

        'name','meta_title', 'meta_desc',

        'c_left1', 'c_left2', 'c_right_top', 'c_right1',

        'c_right2', 'c_right3', 'c_right4', 'c_right5', 'c_right6',

        'c_right1_quest_title', 'c_right_order_title',

        'calc_sub_title', 'c_tab1_title', 'c_tab2_title', 'c_tab3_title', 'c_tab4_title',

        'c_tab5_title', 'c_tab6_title', 'c_tab7_title', 'c_tab8_title', 'c_eff_origin',

        'c_eff_cb', 'c_eff_sepia',

        'calc_tab_1_main_title', 'calc_tab_2_main_title',

        'tear_away_text', 'share_text', 'load_btn_text',

        'choose_pack','seo', 'seo_city_title', 'seo_city_desc',

    ];
}
