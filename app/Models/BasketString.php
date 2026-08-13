<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\BasketString
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $used_bonuses_text
 * @property string|null $used_bonuses_bonuses
 * @property string|null $user_add_place
 * @property string|null $usr_code_place
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $ur_lico
 * @property string|null $ur_reg_num
 * @property string|null $ur_addr
 * @property string|null $ur_pnr_nr
 * @property string|null $ur_bank_name
 * @property string|null $ur_bank_code
 * @property string|null $ur_bank_acc_code
 * @property string|null $coup_app_text
 * @property string|null $thanks_purch
 * @property string|null $we_check_order
 * @property string|null $addr_dost
 * @property string|null $date_dost
 * @property string|null $itog_st
 * @property string|null $lk_text
 * @property string|null $spos_opl
 * @property string|null $contact_pol_nr
 * @property string|null $lk_text2
 * @property string|null $date_dost_jel
 * @property string|null $coup_sale
 * @property string|null $coup_30_40_title
 * @property string|null $coup_30_40_text
 * @property string|null $send_us_print_text_vac
 * @property string|null $user_sale_text
 * @property string|null $sale_for_prev_order
 * @property string|null $coup_on_2_dates
 * @property string|null $receiver_data
 * @property string|null $rec_fio
 * @property string|null $deliv_price
 * @property string|null $agg_text
 * @property string|null $user_city
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|BasketString newModelQuery()
 * @method static Builder|BasketString newQuery()
 * @method static Builder|BasketString query()
 * @method static Builder|BasketString whereAddrDost($value)
 * @method static Builder|BasketString whereAggText($value)
 * @method static Builder|BasketString whereContactPolNr($value)
 * @method static Builder|BasketString whereCoup3040Text($value)
 * @method static Builder|BasketString whereCoup3040Title($value)
 * @method static Builder|BasketString whereCoupAppText($value)
 * @method static Builder|BasketString whereCoupOn2Dates($value)
 * @method static Builder|BasketString whereCoupSale($value)
 * @method static Builder|BasketString whereCreatedAt($value)
 * @method static Builder|BasketString whereDateDost($value)
 * @method static Builder|BasketString whereDateDostJel($value)
 * @method static Builder|BasketString whereDelivPrice($value)
 * @method static Builder|BasketString whereId($value)
 * @method static Builder|BasketString whereItogSt($value)
 * @method static Builder|BasketString whereLkText($value)
 * @method static Builder|BasketString whereLkText2($value)
 * @method static Builder|BasketString whereMetaTitle($value)
 * @method static Builder|BasketString whereRecFio($value)
 * @method static Builder|BasketString whereReceiverData($value)
 * @method static Builder|BasketString whereSaleForPrevOrder($value)
 * @method static Builder|BasketString whereSendUsPrintTextVac($value)
 * @method static Builder|BasketString whereSposOpl($value)
 * @method static Builder|BasketString whereThanksPurch($value)
 * @method static Builder|BasketString whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|BasketString whereUpdatedAt($value)
 * @method static Builder|BasketString whereUrAddr($value)
 * @method static Builder|BasketString whereUrBankAccCode($value)
 * @method static Builder|BasketString whereUrBankCode($value)
 * @method static Builder|BasketString whereUrBankName($value)
 * @method static Builder|BasketString whereUrLico($value)
 * @method static Builder|BasketString whereUrPnrNr($value)
 * @method static Builder|BasketString whereUrRegNum($value)
 * @method static Builder|BasketString whereUsedBonusesBonuses($value)
 * @method static Builder|BasketString whereUsedBonusesText($value)
 * @method static Builder|BasketString whereUserAddPlace($value)
 * @method static Builder|BasketString whereUserCity($value)
 * @method static Builder|BasketString whereUserSaleText($value)
 * @method static Builder|BasketString whereUsrCodePlace($value)
 * @method static Builder|BasketString whereWeCheckOrder($value)
 * @method static Builder|BasketString withTranslation($locale = null, $fallback = true)
 * @method static Builder|BasketString withTranslations($locales = null, $fallback = true)
 */
class BasketString extends Model
{
    use Translatable;

    protected $table = 'basket_strings';

    protected $fillable = [];

    protected $translatable = [
        'meta_title', 'used_bonuses_text', 'used_bonuses_bonuses', 'user_add_place', 'usr_code_place',

        'ur_lico', 'ur_name_l', 'ur_reg_num', 'ur_addr', 'ur_pnr_nr', 'ur_bank_name', 'ur_bank_code', 'ur_bank_acc_code',
        'coup_app_text',

        'thanks_purch', 'we_check_order', 'addr_dost', 'date_dost', 'itog_st', 'lk_text', 'lk_text2', 'spos_opl',
        'contact_pol_nr',

        'date_dost_jel', 'coup_sale', 'coup_30_40_title', 'coup_30_40_text', 'send_us_print_text_vac', 'user_sale_text',
        'sale_for_prev_order', 'coup_on_2_dates', 'receiver_data', 'rec_fio', 'deliv_price', 'agg_text', 'user_city',
    ];
}
