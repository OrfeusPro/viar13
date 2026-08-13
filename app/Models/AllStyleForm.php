<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\AllStyleForm
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $f_title
 * @property string|null $f_desc
 * @property string|null $f_main_title
 * @property string|null $f_step
 * @property string|null $f_step_from
 * @property string|null $f_for
 * @property string|null $f_evt
 * @property string|null $f_fro_who
 * @property string|null $f_next_step_btn
 * @property string|null $f_banner_title
 * @property string|null $f_banner_bot1
 * @property string|null $f_banner_bot2
 * @property string|null $f_banner_bot3
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $enter_email
 * @property string|null $phone
 * @property string|null $foto_portrait
 * @property string|null $load_photo
 * @property string|null $prees_to_load
 * @property string|null $rachet_on
 * @property string|null $send
 * @property string|null $gift_for_you
 * @property string|null $privacy_text
 * @property string|null $skip_quest
 * @property string|null $leave_contact
 * @property string|null $after_end_got
 * @property string|null $succ_thx
 * @property string|null $succ_text
 * @property string|null $succ_write
 * @property string|null $succ_whats_tex
 * @property string|null $succ_whats_link
 * @property string|null $no_select
 * @property string|null $js_zero
 * @property string|null $js_first
 * @property string|null $js_second
 * @property string|null $js_third
 * @property string|null $data_no_file
 * @property string|null $data_no_messager
 * @property string|null $all_done
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|AllStyleForm newModelQuery()
 * @method static Builder|AllStyleForm newQuery()
 * @method static Builder|AllStyleForm query()
 * @method static Builder|AllStyleForm whereAfterEndGot($value)
 * @method static Builder|AllStyleForm whereAllDone($value)
 * @method static Builder|AllStyleForm whereCreatedAt($value)
 * @method static Builder|AllStyleForm whereDataNoFile($value)
 * @method static Builder|AllStyleForm whereDataNoMessager($value)
 * @method static Builder|AllStyleForm whereEnterEmail($value)
 * @method static Builder|AllStyleForm whereFBannerBot1($value)
 * @method static Builder|AllStyleForm whereFBannerBot2($value)
 * @method static Builder|AllStyleForm whereFBannerBot3($value)
 * @method static Builder|AllStyleForm whereFBannerTitle($value)
 * @method static Builder|AllStyleForm whereFDesc($value)
 * @method static Builder|AllStyleForm whereFEvt($value)
 * @method static Builder|AllStyleForm whereFFor($value)
 * @method static Builder|AllStyleForm whereFFroWho($value)
 * @method static Builder|AllStyleForm whereFMainTitle($value)
 * @method static Builder|AllStyleForm whereFNextStepBtn($value)
 * @method static Builder|AllStyleForm whereFStep($value)
 * @method static Builder|AllStyleForm whereFStepFrom($value)
 * @method static Builder|AllStyleForm whereFTitle($value)
 * @method static Builder|AllStyleForm whereFotoPortrait($value)
 * @method static Builder|AllStyleForm whereGiftForYou($value)
 * @method static Builder|AllStyleForm whereId($value)
 * @method static Builder|AllStyleForm whereJsFirst($value)
 * @method static Builder|AllStyleForm whereJsSecond($value)
 * @method static Builder|AllStyleForm whereJsThird($value)
 * @method static Builder|AllStyleForm whereJsZero($value)
 * @method static Builder|AllStyleForm whereLeaveContact($value)
 * @method static Builder|AllStyleForm whereLoadPhoto($value)
 * @method static Builder|AllStyleForm whereNoSelect($value)
 * @method static Builder|AllStyleForm wherePhone($value)
 * @method static Builder|AllStyleForm wherePreesToLoad($value)
 * @method static Builder|AllStyleForm wherePrivacyText($value)
 * @method static Builder|AllStyleForm whereRachetOn($value)
 * @method static Builder|AllStyleForm whereSend($value)
 * @method static Builder|AllStyleForm whereSkipQuest($value)
 * @method static Builder|AllStyleForm whereSuccText($value)
 * @method static Builder|AllStyleForm whereSuccThx($value)
 * @method static Builder|AllStyleForm whereSuccWhatsLink($value)
 * @method static Builder|AllStyleForm whereSuccWhatsTex($value)
 * @method static Builder|AllStyleForm whereSuccWrite($value)
 * @method static Builder|AllStyleForm whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|AllStyleForm whereUpdatedAt($value)
 * @method static Builder|AllStyleForm withTranslation($locale = null, $fallback = true)
 * @method static Builder|AllStyleForm withTranslations($locales = null, $fallback = true)
 */
class AllStyleForm extends Model
{
    use Translatable;

    protected $table = 'all_style_form';

    protected $translatable = [
        'f_title', 'f_desc', 'f_main_title', 'f_step', 'f_step_from', 'f_for', 'f_evt', 'f_fro_who', 'f_next_step_btn',
        'f_banner_title', 'f_banner_bot1', 'f_banner_bot2', 'f_banner_bot3',

        'enter_email', 'phone', 'foto_portrait', 'load_photo', 'prees_to_load', 'rachet_on', 'send', 'gift_for_you',
        'privacy_text', 'skip_quest', 'leave_contact', 'after_end_got', 'succ_thx', 'succ_text', 'succ_write',

        'succ_whats_text', 'succ_whats_link', 'no_select', 'js_zero', 'js_first', 'js_second', 'js_third',
        'data_no_file', 'data_no_messager',

    ];
}
