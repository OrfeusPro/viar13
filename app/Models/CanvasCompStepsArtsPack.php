<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\CanvasCompStepsArtsPack
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $comp_title
 * @property string|null $comp_top_text_block
 * @property string|null $comp_right_form_title
 * @property string|null $comp_f_name_place
 * @property string|null $comp_f_tel_place
 * @property string|null $comp_f_mail_place
 * @property string|null $comp_f_upload_place
 * @property string|null $comp_f_get_btn
 * @property string|null $comp_f_bot_text
 * @property string|null $et_title
 * @property string|null $et1_text
 * @property string|null $et2_text
 * @property string|null $et3_text
 * @property string|null $et4_text
 * @property string|null $et5_text
 * @property string|null $et_left_title
 * @property string|null $et_left_right_text
 * @property string|null $cart_title
 * @property string|null $cart1_title
 * @property string|null $cart2_title
 * @property string|null $cart3_title
 * @property string|null $cart4_title
 * @property string|null $cart5_title
 * @property string|null $cart6_title
 * @property string|null $up_title
 * @property string|null $up_left_text
 * @property string|null $up_def_title
 * @property string|null $up_sizes_list
 * @property string|null $our_work_title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $cart7_title
 * @property string|null $cart8_title
 * @property string|null $comp_text1
 * @property string|null $comp_text2
 * @property string|null $comp_text3
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CanvasCompStepsArtsPack newModelQuery()
 * @method static Builder|CanvasCompStepsArtsPack newQuery()
 * @method static Builder|CanvasCompStepsArtsPack query()
 * @method static Builder|CanvasCompStepsArtsPack whereCart1Title($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCart2Title($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCart3Title($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCart4Title($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCart5Title($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCart6Title($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCart7Title($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCart8Title($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCartTitle($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCompFBotText($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCompFGetBtn($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCompFMailPlace($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCompFNamePlace($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCompFTelPlace($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCompFUploadPlace($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCompRightFormTitle($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCompText1($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCompText2($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCompText3($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCompTitle($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCompTopTextBlock($value)
 * @method static Builder|CanvasCompStepsArtsPack whereCreatedAt($value)
 * @method static Builder|CanvasCompStepsArtsPack whereEt1Text($value)
 * @method static Builder|CanvasCompStepsArtsPack whereEt2Text($value)
 * @method static Builder|CanvasCompStepsArtsPack whereEt3Text($value)
 * @method static Builder|CanvasCompStepsArtsPack whereEt4Text($value)
 * @method static Builder|CanvasCompStepsArtsPack whereEt5Text($value)
 * @method static Builder|CanvasCompStepsArtsPack whereEtLeftRightText($value)
 * @method static Builder|CanvasCompStepsArtsPack whereEtLeftTitle($value)
 * @method static Builder|CanvasCompStepsArtsPack whereEtTitle($value)
 * @method static Builder|CanvasCompStepsArtsPack whereId($value)
 * @method static Builder|CanvasCompStepsArtsPack whereOurWorkTitle($value)
 * @method static Builder|CanvasCompStepsArtsPack whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CanvasCompStepsArtsPack whereUpDefTitle($value)
 * @method static Builder|CanvasCompStepsArtsPack whereUpLeftText($value)
 * @method static Builder|CanvasCompStepsArtsPack whereUpSizesList($value)
 * @method static Builder|CanvasCompStepsArtsPack whereUpTitle($value)
 * @method static Builder|CanvasCompStepsArtsPack whereUpdatedAt($value)
 * @method static Builder|CanvasCompStepsArtsPack withTranslation($locale = null, $fallback = true)
 * @method static Builder|CanvasCompStepsArtsPack withTranslations($locales = null, $fallback = true)
 */
class CanvasCompStepsArtsPack extends Model
{
    use Translatable;

    protected $table = 'canvas_comp_steps_arts_packs';

    protected $fillable = [];

    protected $translatable = [
        'comp_title', 'comp_top_text_block', 'comp_right_form_title', 'comp_f_name_place', 'comp_f_tel_place',
        'comp_f_mail_place', 'comp_f_upload_place', 'comp_f_get_btn', 'comp_f_bot_text', 'et_title', 'et1_text',

        'et2_text', 'et3_text', 'et4_text', 'et5_text', 'et_left_title', 'et_left_right_text', 'cart_title',
        'cart1_title', 'cart2_title', 'cart3_title', 'cart4_title', 'cart5_title', 'cart6_title', 'up_title',
        'up_left_text', 'up_def_title', 'up_sizes_list', 'our_work_title', 'cart7_title', 'cart8_title', 'comp_text1',
        'comp_text2', 'comp_text3',
    ];
}
