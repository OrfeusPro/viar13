<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\PagePartnership
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $meta_desc
 * @property string|null $title
 * @property string|null $top_text
 * @property string|null $partner_item_1
 * @property string|null $partner_item_2
 * @property string|null $partner_item_3
 * @property string|null $sale_from
 * @property string|null $sale_after
 * @property string|null $sale_bot_text
 * @property string|null $sale_link_title
 * @property string|null $sale_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|PagePartnership newModelQuery()
 * @method static Builder|PagePartnership newQuery()
 * @method static Builder|PagePartnership query()
 * @method static Builder|PagePartnership whereCreatedAt($value)
 * @method static Builder|PagePartnership whereId($value)
 * @method static Builder|PagePartnership whereMetaDesc($value)
 * @method static Builder|PagePartnership whereMetaTitle($value)
 * @method static Builder|PagePartnership wherePartnerItem1($value)
 * @method static Builder|PagePartnership wherePartnerItem2($value)
 * @method static Builder|PagePartnership wherePartnerItem3($value)
 * @method static Builder|PagePartnership whereSaleAfter($value)
 * @method static Builder|PagePartnership whereSaleBotText($value)
 * @method static Builder|PagePartnership whereSaleFrom($value)
 * @method static Builder|PagePartnership whereSaleLink($value)
 * @method static Builder|PagePartnership whereSaleLinkTitle($value)
 * @method static Builder|PagePartnership whereTitle($value)
 * @method static Builder|PagePartnership whereTopText($value)
 * @method static Builder|PagePartnership whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|PagePartnership whereUpdatedAt($value)
 * @method static Builder|PagePartnership withTranslation($locale = null, $fallback = true)
 * @method static Builder|PagePartnership withTranslations($locales = null, $fallback = true)
 */
class PageDelivery extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $table = 'page_delivery';

    protected $translatable = [
        'meta_title', 'meta_desc', 'title',
        'first_block_text', 'first_block_box1', 'first_block_box2', 'first_block_box3', 'first_block_box4',
        'second_block_text', 'second_block_text2', 'second_block_box1', 'second_block_box2', 'second_block_box3',
    ];
}
