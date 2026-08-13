<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\GraphPorStylPage
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $meta_desc
 * @property string|null $title
 * @property string|null $sub_title
 * @property string|null $style_title
 * @property string|null $gift_card_title
 * @property string|null $gift_card_text
 * @property string|null $gift_card_order_text
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $read_more
 * @property string|null $price_text
 * @property string|null $from_text
 * @property string|null $photo_req_title
 * @property string|null $photo_req_text1
 * @property string|null $photo_req_text2
 * @property string|null $photo_req_text3
 * @property string|null $photo_req_title2
 * @property string|null $photo_req_text4
 * @property string|null $photo_req_text5
 * @property string|null $price_size_after_form_list
 * @property string|null $img_part_title
 * @property string|null $img_part_text1
 * @property string|null $img_part_text2
 * @property string|null $img_part_text3
 * @property string|null $img_part_text4
 * @property string|null $img_part_text5
 * @property string|null $img_qual_title
 * @property string|null $img_qual_text1
 * @property string|null $img_qual_text2
 * @property string|null $img_qual_text3
 * @property string|null $img_qual_text4
 * @property string|null $img_qual_bot_title
 * @property string|null $img_qual_order_text
 * @property string|null $sharj_link1
 * @property string|null $sharj_link2
 * @property string|null $style_sub_text
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GraphPorStylPage newModelQuery()
 * @method static Builder|GraphPorStylPage newQuery()
 * @method static Builder|GraphPorStylPage query()
 * @method static Builder|GraphPorStylPage whereCreatedAt($value)
 * @method static Builder|GraphPorStylPage whereFromText($value)
 * @method static Builder|GraphPorStylPage whereGiftCardOrderText($value)
 * @method static Builder|GraphPorStylPage whereGiftCardText($value)
 * @method static Builder|GraphPorStylPage whereGiftCardTitle($value)
 * @method static Builder|GraphPorStylPage whereId($value)
 * @method static Builder|GraphPorStylPage whereImgPartText1($value)
 * @method static Builder|GraphPorStylPage whereImgPartText2($value)
 * @method static Builder|GraphPorStylPage whereImgPartText3($value)
 * @method static Builder|GraphPorStylPage whereImgPartText4($value)
 * @method static Builder|GraphPorStylPage whereImgPartText5($value)
 * @method static Builder|GraphPorStylPage whereImgPartTitle($value)
 * @method static Builder|GraphPorStylPage whereImgQualBotTitle($value)
 * @method static Builder|GraphPorStylPage whereImgQualOrderText($value)
 * @method static Builder|GraphPorStylPage whereImgQualText1($value)
 * @method static Builder|GraphPorStylPage whereImgQualText2($value)
 * @method static Builder|GraphPorStylPage whereImgQualText3($value)
 * @method static Builder|GraphPorStylPage whereImgQualText4($value)
 * @method static Builder|GraphPorStylPage whereImgQualTitle($value)
 * @method static Builder|GraphPorStylPage whereMetaDesc($value)
 * @method static Builder|GraphPorStylPage whereMetaTitle($value)
 * @method static Builder|GraphPorStylPage wherePhotoReqText1($value)
 * @method static Builder|GraphPorStylPage wherePhotoReqText2($value)
 * @method static Builder|GraphPorStylPage wherePhotoReqText3($value)
 * @method static Builder|GraphPorStylPage wherePhotoReqText4($value)
 * @method static Builder|GraphPorStylPage wherePhotoReqText5($value)
 * @method static Builder|GraphPorStylPage wherePhotoReqTitle($value)
 * @method static Builder|GraphPorStylPage wherePhotoReqTitle2($value)
 * @method static Builder|GraphPorStylPage wherePriceSizeAfterFormList($value)
 * @method static Builder|GraphPorStylPage wherePriceText($value)
 * @method static Builder|GraphPorStylPage whereReadMore($value)
 * @method static Builder|GraphPorStylPage whereSharjLink1($value)
 * @method static Builder|GraphPorStylPage whereSharjLink2($value)
 * @method static Builder|GraphPorStylPage whereStyleSubText($value)
 * @method static Builder|GraphPorStylPage whereStyleTitle($value)
 * @method static Builder|GraphPorStylPage whereSubTitle($value)
 * @method static Builder|GraphPorStylPage whereTitle($value)
 * @method static Builder|GraphPorStylPage whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GraphPorStylPage whereType($value)
 * @method static Builder|GraphPorStylPage whereUpdatedAt($value)
 * @method static Builder|GraphPorStylPage withTranslation($locale = null, $fallback = true)
 * @method static Builder|GraphPorStylPage withTranslations($locales = null, $fallback = true)
 */
class GraphPorStylPage extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $table = 'graph_por_styl_page';

    protected $translatable = [
        'meta_title', 'meta_desc', 'title', 'sub_title', 'style_sub_text',

        'style_title', 'gift_card_title', 'gift_card_text', 'gift_card_order_text', 'read_more', 'price_text',
        'from_text',

        'photo_req_title', 'photo_req_title2', 'photo_req_text1', 'photo_req_text2', 'photo_req_text3',

        'photo_req_text4', 'photo_req_text5', 'price_size_after_form_list', 'img_part_title', 'img_part_text1',

        'img_part_text2', 'img_part_text3', 'img_part_text4', 'img_part_text5', 'img_qual_title', 'img_qual_text1',

        'img_qual_text2', 'img_qual_text3', 'img_qual_text4', 'img_qual_bot_title', 'img_qual_order_text',

        'sharj_link1', 'sharj_link2',
    ];
}
