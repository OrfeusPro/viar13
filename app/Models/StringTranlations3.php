<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\StringTranlations3
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $standart_text_2
 * @property string|null $express_text_2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $before_after1
 * @property string|null $before_after2
 * @property string|null $before_after3
 * @property string|null $before_after4
 * @property string|null $suc_send_dates
 * @property string|null $show_btn
 * @property string|null $hide_btn
 * @property string|null $area_min
 * @property int|null $coef_1
 * @property int|null $coef_2
 * @property int|null $coef_3
 * @property string|null $area_more
 * @property string|null $help_del_fon
 * @property string|null $help_clear
 * @property string|null $help_del_selected
 * @property string|null $help_del_foto
 * @property string|null $help_back
 * @property string|null $help_forv
 * @property string|null $help_sm
 * @property string|null $help_big
 * @property string|null $help_right
 * @property string|null $help_left
 * @property string|null $help_add_sm
 * @property string|null $help_add_text
 * @property string|null $help_font
 * @property string|null $help_full_screen
 * @property string|null $help_load_all
 * @property string|null $load_btn_text
 * @property string|null $move_scroller
 * @property string|null $user_ex
 * @property string|null $sitemap_text
 * @property string|null $picker_mnth
 * @property string|null $picker_days
 * @property string|null $err_title
 * @property string|null $err_link_text
 * @property string|null $styl_paint_title
 * @property string|null $fb1_text
 * @property string|null $fb2_text
 * @property string|null $inv_size
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|StringTranlations3 newModelQuery()
 * @method static Builder|StringTranlations3 newQuery()
 * @method static Builder|StringTranlations3 query()
 * @method static Builder|StringTranlations3 whereAreaMin($value)
 * @method static Builder|StringTranlations3 whereAreaMore($value)
 * @method static Builder|StringTranlations3 whereBeforeAfter1($value)
 * @method static Builder|StringTranlations3 whereBeforeAfter2($value)
 * @method static Builder|StringTranlations3 whereBeforeAfter3($value)
 * @method static Builder|StringTranlations3 whereBeforeAfter4($value)
 * @method static Builder|StringTranlations3 whereCoef1($value)
 * @method static Builder|StringTranlations3 whereCoef2($value)
 * @method static Builder|StringTranlations3 whereCoef3($value)
 * @method static Builder|StringTranlations3 whereCreatedAt($value)
 * @method static Builder|StringTranlations3 whereErrLinkText($value)
 * @method static Builder|StringTranlations3 whereErrTitle($value)
 * @method static Builder|StringTranlations3 whereExpressText2($value)
 * @method static Builder|StringTranlations3 whereFb1Text($value)
 * @method static Builder|StringTranlations3 whereFb2Text($value)
 * @method static Builder|StringTranlations3 whereHelpAddSm($value)
 * @method static Builder|StringTranlations3 whereHelpAddText($value)
 * @method static Builder|StringTranlations3 whereHelpBack($value)
 * @method static Builder|StringTranlations3 whereHelpBig($value)
 * @method static Builder|StringTranlations3 whereHelpClear($value)
 * @method static Builder|StringTranlations3 whereHelpDelFon($value)
 * @method static Builder|StringTranlations3 whereHelpDelFoto($value)
 * @method static Builder|StringTranlations3 whereHelpDelSelected($value)
 * @method static Builder|StringTranlations3 whereHelpFont($value)
 * @method static Builder|StringTranlations3 whereHelpForv($value)
 * @method static Builder|StringTranlations3 whereHelpFullScreen($value)
 * @method static Builder|StringTranlations3 whereHelpLeft($value)
 * @method static Builder|StringTranlations3 whereHelpLoadAll($value)
 * @method static Builder|StringTranlations3 whereHelpRight($value)
 * @method static Builder|StringTranlations3 whereHelpSm($value)
 * @method static Builder|StringTranlations3 whereHideBtn($value)
 * @method static Builder|StringTranlations3 whereId($value)
 * @method static Builder|StringTranlations3 whereInvSize($value)
 * @method static Builder|StringTranlations3 whereLoadBtnText($value)
 * @method static Builder|StringTranlations3 whereMoveScroller($value)
 * @method static Builder|StringTranlations3 wherePickerDays($value)
 * @method static Builder|StringTranlations3 wherePickerMnth($value)
 * @method static Builder|StringTranlations3 whereShowBtn($value)
 * @method static Builder|StringTranlations3 whereSitemapText($value)
 * @method static Builder|StringTranlations3 whereStandartText2($value)
 * @method static Builder|StringTranlations3 whereStylPaintTitle($value)
 * @method static Builder|StringTranlations3 whereSucSendDates($value)
 * @method static Builder|StringTranlations3 whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|StringTranlations3 whereUpdatedAt($value)
 * @method static Builder|StringTranlations3 whereUserEx($value)
 * @method static Builder|StringTranlations3 withTranslation($locale = null, $fallback = true)
 * @method static Builder|StringTranlations3 withTranslations($locales = null, $fallback = true)
 */
class StringTranlations3 extends Model
{
    use Translatable;

    protected $table = 'string_tranlations3';

    protected $fillable = [];

    protected $translatable = [
        'express_text_2', 'standart_text_2', 'before_after1', 'before_after2', 'before_after3', 'before_after4',
        'suc_send_dates', 'show_btn', 'hide_btn', 'help_del_fon', 'help_clear', 'help_del_selected', 'help_del_foto',

        'help_back', 'help_forv', 'help_sm', 'help_big', 'help_right', 'help_left', 'help_add_sm', 'help_add_text',
        'help_font', 'help_full_screen', 'help_load_all', 'load_btn_text', 'move_scroller', 'user_ex', 'sitemap_text',
        'picker_mnth', 'picker_days',

        'err_link_text', 'err_title', 'styl_paint_title', 'fb1_text', 'fb2_text', 'inv_size',
    ];
}
