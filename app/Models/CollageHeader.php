<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\CollageHeader
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $c_left1
 * @property string|null $c_left2
 * @property string|null $c_right_top
 * @property string|null $c_right1
 * @property string|null $c_right2
 * @property string|null $c_right3
 * @property string|null $c_right4
 * @property string|null $c_right5
 * @property string|null $c_right6
 * @property string|null $c_right1_quest_title
 * @property string|null $c_right_order_title
 * @property string|null $be_glad_coll_title
 * @property string|null $be_glad_coll_sub
 * @property string|null $goto_link_text
 * @property string|null $goto_after_text
 * @property string|null $pop_photo_title
 * @property string|null $order_text_title
 * @property string|null $size_text_title
 * @property string|null $price_text_title
 * @property string|null $from_price_text
 * @property string|null $et_title
 * @property string|null $et1_text
 * @property string|null $et2_text
 * @property string|null $et3_text
 * @property string|null $et4_text
 * @property string|null $et5_text
 * @property string|null $et_left_title
 * @property string|null $et_bot_text
 * @property string|null $our_w_title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $our_works
 * @property string|null $meta_desc
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CollageHeader newModelQuery()
 * @method static Builder|CollageHeader newQuery()
 * @method static Builder|CollageHeader query()
 * @method static Builder|CollageHeader whereBeGladCollSub($value)
 * @method static Builder|CollageHeader whereBeGladCollTitle($value)
 * @method static Builder|CollageHeader whereCLeft1($value)
 * @method static Builder|CollageHeader whereCLeft2($value)
 * @method static Builder|CollageHeader whereCRight1($value)
 * @method static Builder|CollageHeader whereCRight1QuestTitle($value)
 * @method static Builder|CollageHeader whereCRight2($value)
 * @method static Builder|CollageHeader whereCRight3($value)
 * @method static Builder|CollageHeader whereCRight4($value)
 * @method static Builder|CollageHeader whereCRight5($value)
 * @method static Builder|CollageHeader whereCRight6($value)
 * @method static Builder|CollageHeader whereCRightOrderTitle($value)
 * @method static Builder|CollageHeader whereCRightTop($value)
 * @method static Builder|CollageHeader whereCreatedAt($value)
 * @method static Builder|CollageHeader whereEt1Text($value)
 * @method static Builder|CollageHeader whereEt2Text($value)
 * @method static Builder|CollageHeader whereEt3Text($value)
 * @method static Builder|CollageHeader whereEt4Text($value)
 * @method static Builder|CollageHeader whereEt5Text($value)
 * @method static Builder|CollageHeader whereEtBotText($value)
 * @method static Builder|CollageHeader whereEtLeftTitle($value)
 * @method static Builder|CollageHeader whereEtTitle($value)
 * @method static Builder|CollageHeader whereFromPriceText($value)
 * @method static Builder|CollageHeader whereGotoAfterText($value)
 * @method static Builder|CollageHeader whereGotoLinkText($value)
 * @method static Builder|CollageHeader whereId($value)
 * @method static Builder|CollageHeader whereMetaDesc($value)
 * @method static Builder|CollageHeader whereMetaTitle($value)
 * @method static Builder|CollageHeader whereOrderTextTitle($value)
 * @method static Builder|CollageHeader whereOurWTitle($value)
 * @method static Builder|CollageHeader whereOurWorks($value)
 * @method static Builder|CollageHeader wherePopPhotoTitle($value)
 * @method static Builder|CollageHeader wherePriceTextTitle($value)
 * @method static Builder|CollageHeader whereSizeTextTitle($value)
 * @method static Builder|CollageHeader whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CollageHeader whereUpdatedAt($value)
 * @method static Builder|CollageHeader withTranslation($locale = null, $fallback = true)
 * @method static Builder|CollageHeader withTranslations($locales = null, $fallback = true)
 */
class CollageHeader extends Model
{
    use Translatable;

    protected $table = 'collage_header';

    protected $fillable = [];

    protected $translatable = [

        'meta_title', 'meta_desc',

        'c_left1', 'c_left2', 'c_right_top', 'c_right1',

        'c_right2', 'c_right3', 'c_right4', 'c_right5', 'c_right6',

        'c_right1_quest_title', 'c_right_order_title',

        'be_glad_coll_title', 'be_glad_coll_sub', 'goto_link_text',

        'goto_after_text', 'pop_photo_title', 'order_text_title',

        'size_text_price', 'price_text_title', 'from_price_text', 'et_title',

        'et1_text', 'et2_text', 'et3_text', 'et4_text', 'et5_text', 'et_left_title',

        'et_bot_text', 'our_w_title',

    ];
}
