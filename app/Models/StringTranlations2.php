<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\StringTranlations2
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $suc
 * @property string|null $suc_send
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $get_time_to_order
 * @property string|null $our_w_title
 * @property string|null $cons_title
 * @property string|null $cons_link_text
 * @property string|null $cons_bot_text
 * @property string|null $pic_on_wall
 * @property string|null $choose_product_before
 * @property string|null $pack
 * @property string|null $form_num_text
 * @property string|null $orig_image
 * @property string|null $gift_card
 * @property string|null $sender
 * @property string|null $reseiver
 * @property string|null $nominal
 * @property string|null $date
 * @property string|null $torjname
 * @property string|null $torjtext
 * @property string|null $card_type
 * @property string|null $nom_hide
 * @property string|null $nom_show
 * @property string|null $you_have_checked_only
 * @property string|null $hello_text
 * @property string|null $thansk_for_order
 * @property string|null $string_thanks
 * @property string|null $viar_team
 * @property string|null $form_id
 * @property string|null $orig_images
 * @property string|null $welcome_user
 * @property string|null $welcome_your_pass
 * @property string|null $choose_pic
 * @property string|null $share_text
 * @property string|null $status_pending
 * @property string|null $suc_send_fb_sale
 * @property string|null $z_mak_subj
 * @property string|null $z_mak_name
 * @property string|null $z_mak_tel
 * @property string|null $z_mak_imgs
 * @property string|null $z_mak_usr_thanks
 * @property string|null $your_checkour_text
 * @property string|null $act_30_40_title
 * @property string|null $act_dates_title
 * @property string|null $your_act_date_code
 * @property string|null $coup_on_2_dates
 * @property string|null $leave_rev_sub
 * @property string|null $status_watching
 * @property string|null $hud_of_text
 * @property string|null $jel_dost_thanks
 * @property string|null $for_ram_text
 * @property string|null $is_total
 * @property string|null $checkout_text
 * @property string|null $code_gift
 * @property string|null $coup_30_40_text
 * @property string|null $sitemap_title
 * @property string|null $skr_ugl
 * @property string|null $mj_yach
 * @property string|null $foto_rabot
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|StringTranlations2 newModelQuery()
 * @method static Builder|StringTranlations2 newQuery()
 * @method static Builder|StringTranlations2 query()
 * @method static Builder|StringTranlations2 whereAct3040Title($value)
 * @method static Builder|StringTranlations2 whereActDatesTitle($value)
 * @method static Builder|StringTranlations2 whereCardType($value)
 * @method static Builder|StringTranlations2 whereCheckoutText($value)
 * @method static Builder|StringTranlations2 whereChoosePic($value)
 * @method static Builder|StringTranlations2 whereChooseProductBefore($value)
 * @method static Builder|StringTranlations2 whereCodeGift($value)
 * @method static Builder|StringTranlations2 whereConsBotText($value)
 * @method static Builder|StringTranlations2 whereConsLinkText($value)
 * @method static Builder|StringTranlations2 whereConsTitle($value)
 * @method static Builder|StringTranlations2 whereCoup3040Text($value)
 * @method static Builder|StringTranlations2 whereCoupOn2Dates($value)
 * @method static Builder|StringTranlations2 whereCreatedAt($value)
 * @method static Builder|StringTranlations2 whereDate($value)
 * @method static Builder|StringTranlations2 whereForRamText($value)
 * @method static Builder|StringTranlations2 whereFormId($value)
 * @method static Builder|StringTranlations2 whereFormNumText($value)
 * @method static Builder|StringTranlations2 whereFotoRabot($value)
 * @method static Builder|StringTranlations2 whereGetTimeToOrder($value)
 * @method static Builder|StringTranlations2 whereGiftCard($value)
 * @method static Builder|StringTranlations2 whereHelloText($value)
 * @method static Builder|StringTranlations2 whereHudOfText($value)
 * @method static Builder|StringTranlations2 whereId($value)
 * @method static Builder|StringTranlations2 whereIsTotal($value)
 * @method static Builder|StringTranlations2 whereJelDostThanks($value)
 * @method static Builder|StringTranlations2 whereLeaveRevSub($value)
 * @method static Builder|StringTranlations2 whereMjYach($value)
 * @method static Builder|StringTranlations2 whereNomHide($value)
 * @method static Builder|StringTranlations2 whereNomShow($value)
 * @method static Builder|StringTranlations2 whereNominal($value)
 * @method static Builder|StringTranlations2 whereOrigImage($value)
 * @method static Builder|StringTranlations2 whereOrigImages($value)
 * @method static Builder|StringTranlations2 whereOurWTitle($value)
 * @method static Builder|StringTranlations2 wherePack($value)
 * @method static Builder|StringTranlations2 wherePicOnWall($value)
 * @method static Builder|StringTranlations2 whereReseiver($value)
 * @method static Builder|StringTranlations2 whereSender($value)
 * @method static Builder|StringTranlations2 whereShareText($value)
 * @method static Builder|StringTranlations2 whereSitemapTitle($value)
 * @method static Builder|StringTranlations2 whereSkrUgl($value)
 * @method static Builder|StringTranlations2 whereStatusPending($value)
 * @method static Builder|StringTranlations2 whereStatusWatching($value)
 * @method static Builder|StringTranlations2 whereStringThanks($value)
 * @method static Builder|StringTranlations2 whereSuc($value)
 * @method static Builder|StringTranlations2 whereSucSend($value)
 * @method static Builder|StringTranlations2 whereSucSendFbSale($value)
 * @method static Builder|StringTranlations2 whereThanskForOrder($value)
 * @method static Builder|StringTranlations2 whereTorjname($value)
 * @method static Builder|StringTranlations2 whereTorjtext($value)
 * @method static Builder|StringTranlations2 whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|StringTranlations2 whereUpdatedAt($value)
 * @method static Builder|StringTranlations2 whereViarTeam($value)
 * @method static Builder|StringTranlations2 whereWelcomeUser($value)
 * @method static Builder|StringTranlations2 whereWelcomeYourPass($value)
 * @method static Builder|StringTranlations2 whereYouHaveCheckedOnly($value)
 * @method static Builder|StringTranlations2 whereYourActDateCode($value)
 * @method static Builder|StringTranlations2 whereYourCheckourText($value)
 * @method static Builder|StringTranlations2 whereZMakImgs($value)
 * @method static Builder|StringTranlations2 whereZMakName($value)
 * @method static Builder|StringTranlations2 whereZMakSubj($value)
 * @method static Builder|StringTranlations2 whereZMakTel($value)
 * @method static Builder|StringTranlations2 whereZMakUsrThanks($value)
 * @method static Builder|StringTranlations2 withTranslation($locale = null, $fallback = true)
 * @method static Builder|StringTranlations2 withTranslations($locales = null, $fallback = true)
 */
class StringTranlations2 extends Model
{
    use Translatable;

    protected $table = 'string_tranlations2';

    protected $fillable = [];

    protected $translatable = [
        'suc', 'suc_send', 'get_time_to_order', 'our_w_title',

        'cons_title', 'cons_link_text', 'cons_bot_text', 'pic_on_wall', 'choose_product_before', 'pack',

        'form_num_text', 'orig_image',

        'gift_card', 'sender', 'reseiver', 'nominal', 'date', 'torjname', 'torjtext', 'card_type', 'nom_hide',
        'nom_show',

        'you_have_checked_only',

        'hello_text', 'thansk_for_order', 'string_thanks', 'viar_team', 'orig_images', 'welcome_user',
        'welcome_your_pass',

        'please_leave_a_rev',

        'lr_title', 'lr_name', 'lr_ava', 'lr_foto', 'lr_audio', 'lr_text', 'lr_send',

        'choose_pic', 'share_text', 'status_pending', 'suc_send_fb_sale',

        'z_mak_subj', 'z_mak_name', 'z_mak_tel', 'z_mak_imgs', 'z_mak_usr_thanks', 'your_checkour_text',

        'act_30_40_title', 'act_dates_title', 'your_act_date_code', 'coup_on_2_dates', 'status_watching',

        'hud_of_text', 'jel_dost_thanks', 'for_ram_text', 'is_total', 'checkout_text', 'code_gift', 'sitemap_title',
        'mj_yach', 'skr_ugl', 'foto_rabot',
    ];
}
