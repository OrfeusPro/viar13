<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\Stock
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property float|null $friend_sale
 * @property float|null $date_1_sale
 * @property float|null $date_2_sale
 * @property float|null $custom_coupon_sale
 * @property float|null $facebook_sale
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $custom_alr_in_use
 * @property string|null $print_alr_in_use
 * @property string|null $dates_alr_in_use
 * @property string|null $meta_title
 * @property string|null $poss_sales
 * @property string|null $etc_actual_subm
 * @property string|null $modc_title
 * @property string|null $modc_sale
 * @property string|null $fotoc
 * @property string|null $fotoc_sale
 * @property string|null $repr
 * @property string|null $repr_sale
 * @property string|null $dates_sales_text
 * @property string|null $dates_sales_btn_text
 * @property string|null $friend_sale_text
 * @property string|null $friend_sale_btn_text
 * @property string|null $print_text
 * @property string|null $print_btn_text
 * @property string|null $foto_free_text
 * @property string|null $foto_free_btn_text
 * @property string|null $lk_title
 * @property string|null $dates_tip
 * @property string|null $friend_tip
 * @property string|null $print_tip
 * @property string|null $dates_title
 * @property string|null $print_title
 * @property string|null $friend_title
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|Stock newModelQuery()
 * @method static Builder|Stock newQuery()
 * @method static Builder|Stock query()
 * @method static Builder|Stock whereCreatedAt($value)
 * @method static Builder|Stock whereCustomAlrInUse($value)
 * @method static Builder|Stock whereCustomCouponSale($value)
 * @method static Builder|Stock whereDate1Sale($value)
 * @method static Builder|Stock whereDate2Sale($value)
 * @method static Builder|Stock whereDatesAlrInUse($value)
 * @method static Builder|Stock whereDatesSalesBtnText($value)
 * @method static Builder|Stock whereDatesSalesText($value)
 * @method static Builder|Stock whereDatesTip($value)
 * @method static Builder|Stock whereDatesTitle($value)
 * @method static Builder|Stock whereEtcActualSubm($value)
 * @method static Builder|Stock whereFacebookSale($value)
 * @method static Builder|Stock whereFotoFreeBtnText($value)
 * @method static Builder|Stock whereFotoFreeText($value)
 * @method static Builder|Stock whereFotoc($value)
 * @method static Builder|Stock whereFotocSale($value)
 * @method static Builder|Stock whereFriendSale($value)
 * @method static Builder|Stock whereFriendSaleBtnText($value)
 * @method static Builder|Stock whereFriendSaleText($value)
 * @method static Builder|Stock whereFriendTip($value)
 * @method static Builder|Stock whereFriendTitle($value)
 * @method static Builder|Stock whereId($value)
 * @method static Builder|Stock whereLkTitle($value)
 * @method static Builder|Stock whereMetaTitle($value)
 * @method static Builder|Stock whereModcSale($value)
 * @method static Builder|Stock whereModcTitle($value)
 * @method static Builder|Stock wherePossSales($value)
 * @method static Builder|Stock wherePrintAlrInUse($value)
 * @method static Builder|Stock wherePrintBtnText($value)
 * @method static Builder|Stock wherePrintText($value)
 * @method static Builder|Stock wherePrintTip($value)
 * @method static Builder|Stock wherePrintTitle($value)
 * @method static Builder|Stock whereRepr($value)
 * @method static Builder|Stock whereReprSale($value)
 * @method static Builder|Stock whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|Stock whereUpdatedAt($value)
 * @method static Builder|Stock withTranslation($locale = null, $fallback = true)
 * @method static Builder|Stock withTranslations($locales = null, $fallback = true)
 */
class Stock extends Model
{
    use Translatable;

    protected $table = 'stocks';

    protected $translatable = [
        'custom_alr_in_use', 'print_alr_in_use',
		'meta_description', 'title', 
        'dates_alr_in_use', 'meta_title', 'poss_sales', 'etc_actual_subm', 'modc_title', 'modc_sale', 'fotoc',

        'fotoc_sale', 'repr', 'repr_sale', 'dates_sales_text', 'dates_sales_btn_text', 'friend_sale_text',
        'friend_sale_btn_text',

        'print_text', 'print_btn_text', 'foto_free_text', 'foto_free_btn_text', 'lk_title', 'dates_tip', 'friend_tip',
        'print_tip',

        'dates_title', 'print_title', 'friend_title',
    ];
}
