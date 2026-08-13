<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\CanvasPrice
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $title
 * @property string|null $sub_title
 * @property string|null $top_text
 * @property string|null $sub_title3
 * @property string|null $sub_title3_desc
 * @property string|null $bot_text
 * @property string|null $goto_gall
 * @property string|null $goto_gall_link
 * @property string|null $price_title
 * @property string|null $price_list
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CanvasPrice newModelQuery()
 * @method static Builder|CanvasPrice newQuery()
 * @method static Builder|CanvasPrice query()
 * @method static Builder|CanvasPrice whereBotText($value)
 * @method static Builder|CanvasPrice whereCreatedAt($value)
 * @method static Builder|CanvasPrice whereGotoGall($value)
 * @method static Builder|CanvasPrice whereGotoGallLink($value)
 * @method static Builder|CanvasPrice whereId($value)
 * @method static Builder|CanvasPrice wherePriceList($value)
 * @method static Builder|CanvasPrice wherePriceTitle($value)
 * @method static Builder|CanvasPrice whereSubTitle($value)
 * @method static Builder|CanvasPrice whereSubTitle3($value)
 * @method static Builder|CanvasPrice whereSubTitle3Desc($value)
 * @method static Builder|CanvasPrice whereTitle($value)
 * @method static Builder|CanvasPrice whereTopText($value)
 * @method static Builder|CanvasPrice whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CanvasPrice whereUpdatedAt($value)
 * @method static Builder|CanvasPrice withTranslation($locale = null, $fallback = true)
 * @method static Builder|CanvasPrice withTranslations($locales = null, $fallback = true)
 */
class CanvasPrice extends Model
{
    use Translatable;

    protected $translatable = [

        'title',

        'sub_title', 'top_text', 'sub_title2', 'sub_title3',

        'sub_title3_desc', 'bot_text', 'goto_gall', 'goto_gall_link', 'price_title', 'price_list',

    ];
}
