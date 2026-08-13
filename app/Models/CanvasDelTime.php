<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\CanvasDelTime
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $f_title
 * @property string|null $f_text
 * @property string|null $s_title
 * @property string|null $s_text_left
 * @property string|null $s_text_right
 * @property string|null $th_title
 * @property string|null $th_sub_title
 * @property string|null $th_left_text
 * @property string|null $th_right_text
 * @property string|null $th_bot_text
 * @property string|null $th_partners_list
 * @property string|null $for_title
 * @property string|null $for_top_text
 * @property string|null $for_bot_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $att_bot_text
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CanvasDelTime newModelQuery()
 * @method static Builder|CanvasDelTime newQuery()
 * @method static Builder|CanvasDelTime query()
 * @method static Builder|CanvasDelTime whereAttBotText($value)
 * @method static Builder|CanvasDelTime whereCreatedAt($value)
 * @method static Builder|CanvasDelTime whereFText($value)
 * @method static Builder|CanvasDelTime whereFTitle($value)
 * @method static Builder|CanvasDelTime whereForBotText($value)
 * @method static Builder|CanvasDelTime whereForTitle($value)
 * @method static Builder|CanvasDelTime whereForTopText($value)
 * @method static Builder|CanvasDelTime whereId($value)
 * @method static Builder|CanvasDelTime whereSTextLeft($value)
 * @method static Builder|CanvasDelTime whereSTextRight($value)
 * @method static Builder|CanvasDelTime whereSTitle($value)
 * @method static Builder|CanvasDelTime whereThBotText($value)
 * @method static Builder|CanvasDelTime whereThLeftText($value)
 * @method static Builder|CanvasDelTime whereThPartnersList($value)
 * @method static Builder|CanvasDelTime whereThRightText($value)
 * @method static Builder|CanvasDelTime whereThSubTitle($value)
 * @method static Builder|CanvasDelTime whereThTitle($value)
 * @method static Builder|CanvasDelTime whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CanvasDelTime whereUpdatedAt($value)
 * @method static Builder|CanvasDelTime withTranslation($locale = null, $fallback = true)
 * @method static Builder|CanvasDelTime withTranslations($locales = null, $fallback = true)
 */
class CanvasDelTime extends Model
{
    use Translatable;

    protected $table = 'canvas_del_time';

    protected $fillable = [];

    protected $translatable = [
        'f_title', 'f_text', 's_title', 's_text_left', 's_text_right', 'th_title', 'th_sub_title', 'th_left_text',
        'th_right_text', 'th_bot_text', 'th_partners_list',

        'for_title', 'for_top_text', 'for_bot_text', 'att_bot_text',
    ];
}
