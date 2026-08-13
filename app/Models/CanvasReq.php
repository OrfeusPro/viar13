<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\CanvasReq
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $req_title
 * @property string|null $req_item1
 * @property string|null $req_item2
 * @property string|null $req_item3
 * @property string|null $req_photo_title
 * @property string|null $req_photo_1
 * @property string|null $req_photo_2
 * @property string|null $req_photo_3
 * @property string|null $req_photo_4
 * @property string|null $req_img_title
 * @property string|null $req_img_text
 * @property string|null $req_img_sub_title
 * @property string|null $req_img_left_text
 * @property string|null $req_img_right_title
 * @property string|null $req_img_right_text
 * @property string|null $req_search_right_title
 * @property string|null $req_ins_filter
 * @property string|null $req_ready_links_title
 * @property string|null $req_link1_img
 * @property string|null $req_link1_title
 * @property string|null $req_link1_lnk
 * @property string|null $req_link2_img
 * @property string|null $req_link2_title
 * @property string|null $req_link2_link
 * @property string|null $req_link3_img
 * @property string|null $req_link3_title
 * @property string|null $req_link3_link
 * @property string|null $req_link4_title
 * @property string|null $req_link4_img
 * @property string|null $req_link4_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $req_blank_pay
 * @property string|null $req_int_free
 * @property string|null $req_zap__right_title
 * @property string|null $req_zap_right_text
 * @property string|null $req_search_left_text
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CanvasReq newModelQuery()
 * @method static Builder|CanvasReq newQuery()
 * @method static Builder|CanvasReq query()
 * @method static Builder|CanvasReq whereCreatedAt($value)
 * @method static Builder|CanvasReq whereId($value)
 * @method static Builder|CanvasReq whereReqBlankPay($value)
 * @method static Builder|CanvasReq whereReqImgLeftText($value)
 * @method static Builder|CanvasReq whereReqImgRightText($value)
 * @method static Builder|CanvasReq whereReqImgRightTitle($value)
 * @method static Builder|CanvasReq whereReqImgSubTitle($value)
 * @method static Builder|CanvasReq whereReqImgText($value)
 * @method static Builder|CanvasReq whereReqImgTitle($value)
 * @method static Builder|CanvasReq whereReqInsFilter($value)
 * @method static Builder|CanvasReq whereReqIntFree($value)
 * @method static Builder|CanvasReq whereReqItem1($value)
 * @method static Builder|CanvasReq whereReqItem2($value)
 * @method static Builder|CanvasReq whereReqItem3($value)
 * @method static Builder|CanvasReq whereReqLink1Img($value)
 * @method static Builder|CanvasReq whereReqLink1Lnk($value)
 * @method static Builder|CanvasReq whereReqLink1Title($value)
 * @method static Builder|CanvasReq whereReqLink2Img($value)
 * @method static Builder|CanvasReq whereReqLink2Link($value)
 * @method static Builder|CanvasReq whereReqLink2Title($value)
 * @method static Builder|CanvasReq whereReqLink3Img($value)
 * @method static Builder|CanvasReq whereReqLink3Link($value)
 * @method static Builder|CanvasReq whereReqLink3Title($value)
 * @method static Builder|CanvasReq whereReqLink4Img($value)
 * @method static Builder|CanvasReq whereReqLink4Link($value)
 * @method static Builder|CanvasReq whereReqLink4Title($value)
 * @method static Builder|CanvasReq whereReqPhoto1($value)
 * @method static Builder|CanvasReq whereReqPhoto2($value)
 * @method static Builder|CanvasReq whereReqPhoto3($value)
 * @method static Builder|CanvasReq whereReqPhoto4($value)
 * @method static Builder|CanvasReq whereReqPhotoTitle($value)
 * @method static Builder|CanvasReq whereReqReadyLinksTitle($value)
 * @method static Builder|CanvasReq whereReqSearchLeftText($value)
 * @method static Builder|CanvasReq whereReqSearchRightTitle($value)
 * @method static Builder|CanvasReq whereReqTitle($value)
 * @method static Builder|CanvasReq whereReqZapRightText($value)
 * @method static Builder|CanvasReq whereReqZapRightTitle($value)
 * @method static Builder|CanvasReq whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CanvasReq whereUpdatedAt($value)
 * @method static Builder|CanvasReq withTranslation($locale = null, $fallback = true)
 * @method static Builder|CanvasReq withTranslations($locales = null, $fallback = true)
 */
class CanvasReq extends Model
{
    use Translatable;

    protected $table = 'canvas_req';

    protected $fillable = [];

    protected $translatable = [

        'req_title',

        'req_item1', 'req_item2', 'req_item3', 'req_photo_title',

        'req_photo_1', 'req_photo_2', 'req_photo_3', 'req_photo_4', 'req_img_title', 'req_img_text',

        'req_img_sub_title', 'req_img_left_text', 'req_img_right_title', 'req_img_right_text',

        'req_search_right_title', 'req_search_left_text',

        'req_search_right_text', 'req_ready_links_title', 'req_link1_title', 'req_link1_lnk',

        'req_link2_title', 'req_link2_link', 'req_link3_title', 'req_link3_link',

        'req_link4_title', 'req_link4_link',

        'req_blank_pay', 'req_int_free',

        'req_zap_right_title', 'req_zap_right_text', 'req_ins_filter',

    ];
}
