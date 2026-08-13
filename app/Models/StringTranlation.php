<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\StringTranlation
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $wall_size
 * @property string|null $pic_size
 * @property string|null $rama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $req_field
 * @property string|null $price_text
 * @property string|null $price_from_text
 * @property string|null $graph_portrait
 * @property string|null $gallery
 * @property string|null $create_your_art
 * @property string|null $what_is_text
 * @property string|null $open
 * @property string|null $hide
 * @property string|null $diff_sizes
 * @property string|null $cm
 * @property string|null $enter_people_count
 * @property string|null $total_price
 * @property string|null $all_our_works
 * @property string|null $read_more
 * @property string|null $etc_styles
 * @property string|null $sh_title
 * @property string|null $sh_sub
 * @property string|null $sh_all_obr
 * @property string|null $group_obr
 * @property string|null $group_obr_subtext
 * @property string|null $all_obr
 * @property string|null $before_after_w_title
 * @property string|null $before_after_subtitle
 * @property string|null $before_after_text
 * @property string|null $report_suc_send
 * @property string|null $too_big_filesize
 * @property string|null $inv_filesize_or_ext
 * @property string|null $media_missing
 * @property string|null $pic_on_wall
 * @property string|null $choose_person_count
 * @property string|null $personal
 * @property string|null $group
 * @property string|null $chel
 * @property string|null $enter_person_count
 * @property string|null $send_text
 * @property string|null $hud_of_name
 * @property string|null $choose_complect
 * @property string|null $comments
 * @property string|null $put_away
 * @property string|null $share
 * @property string|null $itog_price
 * @property string|null $order_btn
 * @property string|null $srok_izg
 * @property string|null $standart_text
 * @property string|null $express_text
 * @property float|null $standart_price
 * @property float|null $express_price
 * @property string|null $err_price
 * @property string|null $req_size
 * @property string|null $bask_form
 * @property string|null $bask_persons
 * @property string|null $bask_isp
 * @property string|null $bask_holst
 * @property string|null $bask_of
 * @property string|null $bask_ram
 * @property string|null $bask_izg
 * @property string|null $size_text
 * @property string|null $dd_text
 * @property string|null $hh_text
 * @property string|null $mm_text
 * @property string|null $ss_text
 * @property string|null $sens_us_print_text_vac
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|StringTranlation newModelQuery()
 * @method static Builder|StringTranlation newQuery()
 * @method static Builder|StringTranlation query()
 * @method static Builder|StringTranlation whereAllObr($value)
 * @method static Builder|StringTranlation whereAllOurWorks($value)
 * @method static Builder|StringTranlation whereBaskForm($value)
 * @method static Builder|StringTranlation whereBaskHolst($value)
 * @method static Builder|StringTranlation whereBaskIsp($value)
 * @method static Builder|StringTranlation whereBaskIzg($value)
 * @method static Builder|StringTranlation whereBaskOf($value)
 * @method static Builder|StringTranlation whereBaskPersons($value)
 * @method static Builder|StringTranlation whereBaskRam($value)
 * @method static Builder|StringTranlation whereBeforeAfterSubtitle($value)
 * @method static Builder|StringTranlation whereBeforeAfterText($value)
 * @method static Builder|StringTranlation whereBeforeAfterWTitle($value)
 * @method static Builder|StringTranlation whereChel($value)
 * @method static Builder|StringTranlation whereChooseComplect($value)
 * @method static Builder|StringTranlation whereChoosePersonCount($value)
 * @method static Builder|StringTranlation whereCm($value)
 * @method static Builder|StringTranlation whereComments($value)
 * @method static Builder|StringTranlation whereCreateYourArt($value)
 * @method static Builder|StringTranlation whereCreatedAt($value)
 * @method static Builder|StringTranlation whereDdText($value)
 * @method static Builder|StringTranlation whereDiffSizes($value)
 * @method static Builder|StringTranlation whereEnterPeopleCount($value)
 * @method static Builder|StringTranlation whereEnterPersonCount($value)
 * @method static Builder|StringTranlation whereErrPrice($value)
 * @method static Builder|StringTranlation whereEtcStyles($value)
 * @method static Builder|StringTranlation whereExpressPrice($value)
 * @method static Builder|StringTranlation whereExpressText($value)
 * @method static Builder|StringTranlation whereGallery($value)
 * @method static Builder|StringTranlation whereGraphPortrait($value)
 * @method static Builder|StringTranlation whereGroup($value)
 * @method static Builder|StringTranlation whereGroupObr($value)
 * @method static Builder|StringTranlation whereGroupObrSubtext($value)
 * @method static Builder|StringTranlation whereHhText($value)
 * @method static Builder|StringTranlation whereHide($value)
 * @method static Builder|StringTranlation whereHudOfName($value)
 * @method static Builder|StringTranlation whereId($value)
 * @method static Builder|StringTranlation whereInvFilesizeOrExt($value)
 * @method static Builder|StringTranlation whereItogPrice($value)
 * @method static Builder|StringTranlation whereMediaMissing($value)
 * @method static Builder|StringTranlation whereMmText($value)
 * @method static Builder|StringTranlation whereOpen($value)
 * @method static Builder|StringTranlation whereOrderBtn($value)
 * @method static Builder|StringTranlation wherePersonal($value)
 * @method static Builder|StringTranlation wherePicOnWall($value)
 * @method static Builder|StringTranlation wherePicSize($value)
 * @method static Builder|StringTranlation wherePriceFromText($value)
 * @method static Builder|StringTranlation wherePriceText($value)
 * @method static Builder|StringTranlation wherePutAway($value)
 * @method static Builder|StringTranlation whereRama($value)
 * @method static Builder|StringTranlation whereReadMore($value)
 * @method static Builder|StringTranlation whereReportSucSend($value)
 * @method static Builder|StringTranlation whereReqField($value)
 * @method static Builder|StringTranlation whereReqSize($value)
 * @method static Builder|StringTranlation whereSendText($value)
 * @method static Builder|StringTranlation whereSensUsPrintTextVac($value)
 * @method static Builder|StringTranlation whereShAllObr($value)
 * @method static Builder|StringTranlation whereShSub($value)
 * @method static Builder|StringTranlation whereShTitle($value)
 * @method static Builder|StringTranlation whereShare($value)
 * @method static Builder|StringTranlation whereSizeText($value)
 * @method static Builder|StringTranlation whereSrokIzg($value)
 * @method static Builder|StringTranlation whereSsText($value)
 * @method static Builder|StringTranlation whereStandartPrice($value)
 * @method static Builder|StringTranlation whereStandartText($value)
 * @method static Builder|StringTranlation whereTooBigFilesize($value)
 * @method static Builder|StringTranlation whereTotalPrice($value)
 * @method static Builder|StringTranlation whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|StringTranlation whereUpdatedAt($value)
 * @method static Builder|StringTranlation whereWallSize($value)
 * @method static Builder|StringTranlation whereWhatIsText($value)
 * @method static Builder|StringTranlation withTranslation($locale = null, $fallback = true)
 * @method static Builder|StringTranlation withTranslations($locales = null, $fallback = true)
 */
class StringTranlation extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $translatable = [
        'wall_size', 'pic_size', 'rama', 'req_field', 'price_text', 'price_from_text',

        'graph_portrait', 'gallery', 'create_your_art', 'what_is_text', 'open', 'hide', 'diff_sizes', 'cm',
        'enter_people_count',

        'total_price', 'all_our_works', 'read_more', 'etc_styles', 'sh_title', 'sh_sub', 'sh_all_obr', 'group_obr',
        'group_obr_subtext', 'all_obr', 'before_after_w_title', 'before_after_subtitle', 'before_after_text',

        'report_suc_send', 'too_big_filesize', 'inv_filesize_or_ext', 'media_missing', 'pic_on_wall',

        'choose_person_count', 'personal', 'group', 'chel', 'enter_person_count', 'send_text', 'hud_of_name',
        'choose_complect',

        'comments', 'put_away', 'share', 'itog_price', 'order_btn', 'srok_izg', 'standart_text', 'express_text',

        'err_price', 'req_size',

        'bask_form', 'bask_persons', 'bask_isp', 'bask_holst', 'bask_of', 'bask_ram', 'bask_izg',

        'size_text', 'dd_text', 'hh_text', 'mm_text', 'ss_text', 'sens_us_print_text_vac',

        'suc_send', 'suc', 'leave_rev_sub', 'leave_rev_meta_title', 'leave_rev_title', 'leave_rev_name',
        'leave_rev_email',

        'leave_rev_ava', 'leave_rev_promo', 'leave_rev_audio', 'leave_rev_text',
    ];
}
