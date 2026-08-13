<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\OrderString
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $consignor_place
 * @property string|null $consignor_text
 * @property string|null $req_num_place
 * @property string|null $req_num_text
 * @property string|null $addr_place
 * @property string|null $addr_text
 * @property string|null $pvn_place
 * @property string|null $pvn_text
 * @property string|null $bank_name_place
 * @property string|null $bank_name_text
 * @property string|null $office_addr_place
 * @property string|null $office_addr_text
 * @property string|null $acc_num_place
 * @property string|null $acc_num_text
 * @property string|null $customer_place
 * @property string|null $reg_num_place
 * @property string|null $legal_addr_place
 * @property string|null $prn_nr_place
 * @property string|null $bank_name_place2
 * @property string|null $bank_code2
 * @property string|null $deliv_addr_place
 * @property string|null $code_place
 * @property string|null $prod_nr_place
 * @property string|null $prod_name_place
 * @property string|null $prod_unit_place
 * @property string|null $prod_qty_place
 * @property string|null $prod_price_place
 * @property string|null $prod_am_place
 * @property string|null $total_place
 * @property string|null $total_am_place
 * @property string|null $persons_bot_left
 * @property string|null $persons_bot_right
 * @property string|null $sign_place
 * @property float|null $nds
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $bank_code_place
 * @property string|null $bank_code_text
 * @property string|null $admin_name
 * @property string|null $persons_bot_left_name
 * @property string|null $persons_bot_right_name
 * @property string|null $order_vr
 * @property string|null $deliv_price
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|OrderString newModelQuery()
 * @method static Builder|OrderString newQuery()
 * @method static Builder|OrderString query()
 * @method static Builder|OrderString whereAccNumPlace($value)
 * @method static Builder|OrderString whereAccNumText($value)
 * @method static Builder|OrderString whereAddrPlace($value)
 * @method static Builder|OrderString whereAddrText($value)
 * @method static Builder|OrderString whereAdminName($value)
 * @method static Builder|OrderString whereBankCode2($value)
 * @method static Builder|OrderString whereBankCodePlace($value)
 * @method static Builder|OrderString whereBankCodeText($value)
 * @method static Builder|OrderString whereBankNamePlace($value)
 * @method static Builder|OrderString whereBankNamePlace2($value)
 * @method static Builder|OrderString whereBankNameText($value)
 * @method static Builder|OrderString whereCodePlace($value)
 * @method static Builder|OrderString whereConsignorPlace($value)
 * @method static Builder|OrderString whereConsignorText($value)
 * @method static Builder|OrderString whereCreatedAt($value)
 * @method static Builder|OrderString whereCustomerPlace($value)
 * @method static Builder|OrderString whereDelivAddrPlace($value)
 * @method static Builder|OrderString whereDelivPrice($value)
 * @method static Builder|OrderString whereId($value)
 * @method static Builder|OrderString whereLegalAddrPlace($value)
 * @method static Builder|OrderString whereNds($value)
 * @method static Builder|OrderString whereOfficeAddrPlace($value)
 * @method static Builder|OrderString whereOfficeAddrText($value)
 * @method static Builder|OrderString whereOrderVr($value)
 * @method static Builder|OrderString wherePersonsBotLeft($value)
 * @method static Builder|OrderString wherePersonsBotLeftName($value)
 * @method static Builder|OrderString wherePersonsBotRight($value)
 * @method static Builder|OrderString wherePersonsBotRightName($value)
 * @method static Builder|OrderString wherePrnNrPlace($value)
 * @method static Builder|OrderString whereProdAmPlace($value)
 * @method static Builder|OrderString whereProdNamePlace($value)
 * @method static Builder|OrderString whereProdNrPlace($value)
 * @method static Builder|OrderString whereProdPricePlace($value)
 * @method static Builder|OrderString whereProdQtyPlace($value)
 * @method static Builder|OrderString whereProdUnitPlace($value)
 * @method static Builder|OrderString wherePvnPlace($value)
 * @method static Builder|OrderString wherePvnText($value)
 * @method static Builder|OrderString whereRegNumPlace($value)
 * @method static Builder|OrderString whereReqNumPlace($value)
 * @method static Builder|OrderString whereReqNumText($value)
 * @method static Builder|OrderString whereSignPlace($value)
 * @method static Builder|OrderString whereTotalAmPlace($value)
 * @method static Builder|OrderString whereTotalPlace($value)
 * @method static Builder|OrderString whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|OrderString whereUpdatedAt($value)
 * @method static Builder|OrderString withTranslation($locale = null, $fallback = true)
 * @method static Builder|OrderString withTranslations($locales = null, $fallback = true)
 */
class OrderString extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $table = 'order_strings';

    protected $translatable = [
        'consignor_place', 'consignor_text', 'req_num_place', 'req_num_text', 'addr_place', 'addr_text', 'pvn_place',
        'pvn_text', 'bank_name_place', 'bank_name_text', 'office_addr_place', 'office_addr_text', 'acc_num_place',
        'acc_num_text', 'customer_place', 'reg_num_place', 'legal_addr_place', 'bank_name_place2', 'cel-platezha',
        'bank_code2',

        'deliv_addr_place', 'code_place', 'prod_nr_place', 'prod_name_place', 'prod_unit_place', 'prod_qty_place',
        'prod_price_place',

        'prod_am_place', 'total_place', 'total_am_place', 'persons_bot_left', 'persons_bot_right', 'sign_place',

        'bank_code_place', 'bank_code_text', 'admin_name', 'date_dost_jel', 'persons_bot_left_name',
        'persons_bot_right_name', 'order_vr',

        'deliv_price',
        
        'consignor_text_vrv', 'addr_text_vrv', 'bank_name_text_vrv', 'addr2_text_vrv', 'req_num_text_vrv', 'pvn_text_vrv', 'acc_num_text_vrv',
        'consignor_text_vrr', 'addr_text_vrr', 'bank_name_text_vrr', 'office_addr_text_vrr', 'req_num_text_vrr', 'pvn_text_vrr', 'acc_num_text_vrr',
        'consignor_text_vra', 'addr_text_vra', 'bank_name_text_vra', 'addr2_text_vra', 'req_num_text_vra', 'pvn_text_vra', 'acc_num_text_vra'
    ];
}