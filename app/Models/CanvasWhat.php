<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\CanvasWhat
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $title
 * @property string|null $top_text
 * @property string|null $left_text
 * @property string|null $right_text
 * @property string|null $bot_left_text
 * @property string|null $bot_right_text
 * @property string|null $bot_bot_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CanvasWhat newModelQuery()
 * @method static Builder|CanvasWhat newQuery()
 * @method static Builder|CanvasWhat query()
 * @method static Builder|CanvasWhat whereBotBotText($value)
 * @method static Builder|CanvasWhat whereBotLeftText($value)
 * @method static Builder|CanvasWhat whereBotRightText($value)
 * @method static Builder|CanvasWhat whereCreatedAt($value)
 * @method static Builder|CanvasWhat whereId($value)
 * @method static Builder|CanvasWhat whereLeftText($value)
 * @method static Builder|CanvasWhat whereRightText($value)
 * @method static Builder|CanvasWhat whereTitle($value)
 * @method static Builder|CanvasWhat whereTopText($value)
 * @method static Builder|CanvasWhat whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CanvasWhat whereUpdatedAt($value)
 * @method static Builder|CanvasWhat withTranslation($locale = null, $fallback = true)
 * @method static Builder|CanvasWhat withTranslations($locales = null, $fallback = true)
 */
class CanvasWhat extends Model
{
    use Translatable;

    protected $table = 'canvas_what';

    protected $fillable = [];

    protected $translatable = [
        'meta_title', 'title', 'top_text', 'left_text', 'right_text', 'bot_left_text', 'bot_right_text', 'bot_bot_text'
    ];
}
