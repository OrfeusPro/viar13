<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\UserMessage
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $rev_mail
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $rev_subject
 * @property string|null $rev_fb
 * @property string|null $rev_vk
 * @property string|null $rev_ig
 * @property string|null $rev_yb
 * @property string|null $rev_title
 * @property string|null $rev_sub_title
 * @property string|null $rev_btn_text
 * @property string|null $rev_thx_text
 * @property string|null $rev_after_txt_user
 * @property string|null $rev_mai
 * @property string|null $sale_30_subject
 * @property string|null $sale_30_title
 * @property string|null $sale_30_text
 * @property string|null $sale_btn_text
 * @property string|null $your_order_given
 * @property string|null $your_order_given_foto
 * @property string|null $your_order_z_data
 * @property string|null $your_order_subject
 * @property string|null $your_rev__was_added_subject
 * @property string|null $your_rev__was_added_text
 * @property string|null $your_rev__was_added_title
 * @property string|null $your_rev__was_added_see_title
 * @property string|null $we_check_title
 * @property string|null $we_check_sub
 * @property string|null $sended_subject
 * @property string|null $sended_sub
 * @property string|null $sended_noty
 * @property string|null $sended_date_zak
 * @property string|null $sended_date_otp
 * @property string|null $sended_date_jel
 * @property string|null $sale_30_40_subj
 * @property string|null $sale_30_40_title
 * @property string|null $sale_30_40_text
 * @property string|null $sale_30_40_btn
 * @property string|null $sale_added
 * @property string|null $sale_added_text
 * @property string|null $uns_text
 * @property string|null $reg_sub
 * @property string|null $reg_title
 * @property string|null $reg_text
 * @property string|null $reg_enter_pass
 * @property string|null $jel_dat_dost
 * @property string|null $tip_zakaz_sr
 * @property string|null $tip_zakaz_ob
 * @property string|null $data_zakaza
 * @property string|null $admin_user_chat_title
 * @property string|null $new_painter_order
 * @property string|null $new_painter_order_text
 * @property string|null $new_photo_subject
 * @property string|null $new_photo_text
 * @property string|null $user_painter_mail_subject
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|UserMessage newModelQuery()
 * @method static Builder|UserMessage newQuery()
 * @method static Builder|UserMessage query()
 * @method static Builder|UserMessage whereAdminUserChatTitle($value)
 * @method static Builder|UserMessage whereCreatedAt($value)
 * @method static Builder|UserMessage whereDataZakaza($value)
 * @method static Builder|UserMessage whereId($value)
 * @method static Builder|UserMessage whereJelDatDost($value)
 * @method static Builder|UserMessage whereNewPainterOrder($value)
 * @method static Builder|UserMessage whereNewPainterOrderText($value)
 * @method static Builder|UserMessage whereNewPhotoSubject($value)
 * @method static Builder|UserMessage whereNewPhotoText($value)
 * @method static Builder|UserMessage whereRegEnterPass($value)
 * @method static Builder|UserMessage whereRegSub($value)
 * @method static Builder|UserMessage whereRegText($value)
 * @method static Builder|UserMessage whereRegTitle($value)
 * @method static Builder|UserMessage whereRevAfterTxtUser($value)
 * @method static Builder|UserMessage whereRevBtnText($value)
 * @method static Builder|UserMessage whereRevFb($value)
 * @method static Builder|UserMessage whereRevIg($value)
 * @method static Builder|UserMessage whereRevMai($value)
 * @method static Builder|UserMessage whereRevMail($value)
 * @method static Builder|UserMessage whereRevSubTitle($value)
 * @method static Builder|UserMessage whereRevSubject($value)
 * @method static Builder|UserMessage whereRevThxText($value)
 * @method static Builder|UserMessage whereRevTitle($value)
 * @method static Builder|UserMessage whereRevVk($value)
 * @method static Builder|UserMessage whereRevYb($value)
 * @method static Builder|UserMessage whereSale3040Btn($value)
 * @method static Builder|UserMessage whereSale3040Subj($value)
 * @method static Builder|UserMessage whereSale3040Text($value)
 * @method static Builder|UserMessage whereSale3040Title($value)
 * @method static Builder|UserMessage whereSale30Subject($value)
 * @method static Builder|UserMessage whereSale30Text($value)
 * @method static Builder|UserMessage whereSale30Title($value)
 * @method static Builder|UserMessage whereSaleAdded($value)
 * @method static Builder|UserMessage whereSaleAddedText($value)
 * @method static Builder|UserMessage whereSaleBtnText($value)
 * @method static Builder|UserMessage whereSendedDateJel($value)
 * @method static Builder|UserMessage whereSendedDateOtp($value)
 * @method static Builder|UserMessage whereSendedDateZak($value)
 * @method static Builder|UserMessage whereSendedNoty($value)
 * @method static Builder|UserMessage whereSendedSub($value)
 * @method static Builder|UserMessage whereSendedSubject($value)
 * @method static Builder|UserMessage whereTipZakazOb($value)
 * @method static Builder|UserMessage whereTipZakazSr($value)
 * @method static Builder|UserMessage whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|UserMessage whereUnsText($value)
 * @method static Builder|UserMessage whereUpdatedAt($value)
 * @method static Builder|UserMessage whereUserPainterMailSubject($value)
 * @method static Builder|UserMessage whereWeCheckSub($value)
 * @method static Builder|UserMessage whereWeCheckTitle($value)
 * @method static Builder|UserMessage whereYourOrderGiven($value)
 * @method static Builder|UserMessage whereYourOrderGivenFoto($value)
 * @method static Builder|UserMessage whereYourOrderSubject($value)
 * @method static Builder|UserMessage whereYourOrderZData($value)
 * @method static Builder|UserMessage whereYourRevWasAddedSeeTitle($value)
 * @method static Builder|UserMessage whereYourRevWasAddedSubject($value)
 * @method static Builder|UserMessage whereYourRevWasAddedText($value)
 * @method static Builder|UserMessage whereYourRevWasAddedTitle($value)
 * @method static Builder|UserMessage withTranslation($locale = null, $fallback = true)
 * @method static Builder|UserMessage withTranslations($locales = null, $fallback = true)
 */
class UserMessage extends Model
{
    use Translatable;

    protected $table = 'user_messages';

    protected $translatable = [
        'rev_mail', 'rev_subject', 'rev_fb', 'rev_vk', 'rev_ig', 'rev_yb', 'rev_title', 'rev_sub_title', 'rev_btn_text',
        'rev_thx_text', 'rev_after_txt_user',

        'sale_30_subject', 'sale_30_title', 'sale_30_text', 'sale_btn_text',

        'your_order_given_foto', 'your_order_given', 'your_order_z_data', 'your_order_subject',

        'your_rev__was_added_subject', 'your_rev__was_added_text', 'your_rev__was_added_title',

        'your_rev__was_added_see_title', 'we_check_title', 'we_check_sub', 'sended_subject', 'sended_sub',
        'sended_noty', 'sended_date_zak',

        'sended_date_otp', 'sended_date_jel', 'sale_30_40_subj', 'sale_30_40_title', 'sale_30_40_text',
        'sale_30_40_btn', 'sale_added_text', 'sale_added', 'uns_text', 'reg_sub', 'reg_title', 'reg_text',
        'reg_enter_pass',

        'jel_dat_dost', 'tip_zakaz_sr', 'tip_zakaz_ob', 'data_zakaza', 'admin_order_title', 'admin_order_text',
        'admin_user_chat_title',

        'new_painter_order', 'new_painter_order_text', 'new_photo_subject', 'new_photo_text', 'sale_date',

        'tracking_number',
    ];
}
