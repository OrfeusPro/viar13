<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\OilWhatReq
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $what_title
 * @property string|null $what_desc
 * @property string|null $show_title
 * @property string|null $hide_title
 * @property string|null $req_title
 * @property string|null $req_top_text
 * @property string|null $req_block_2_title
 * @property string|null $req_list_block1_text
 * @property string|null $req_list_block2_text
 * @property string|null $req_list_block3_text
 * @property string|null $req_left_sub
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|OilWhatReq newModelQuery()
 * @method static Builder|OilWhatReq newQuery()
 * @method static Builder|OilWhatReq query()
 * @method static Builder|OilWhatReq whereCreatedAt($value)
 * @method static Builder|OilWhatReq whereHideTitle($value)
 * @method static Builder|OilWhatReq whereId($value)
 * @method static Builder|OilWhatReq whereReqBlock2Title($value)
 * @method static Builder|OilWhatReq whereReqLeftSub($value)
 * @method static Builder|OilWhatReq whereReqListBlock1Text($value)
 * @method static Builder|OilWhatReq whereReqListBlock2Text($value)
 * @method static Builder|OilWhatReq whereReqListBlock3Text($value)
 * @method static Builder|OilWhatReq whereReqTitle($value)
 * @method static Builder|OilWhatReq whereReqTopText($value)
 * @method static Builder|OilWhatReq whereShowTitle($value)
 * @method static Builder|OilWhatReq whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|OilWhatReq whereUpdatedAt($value)
 * @method static Builder|OilWhatReq whereWhatDesc($value)
 * @method static Builder|OilWhatReq whereWhatTitle($value)
 * @method static Builder|OilWhatReq withTranslation($locale = null, $fallback = true)
 * @method static Builder|OilWhatReq withTranslations($locales = null, $fallback = true)
 */
class OilWhatReq extends Model
{
    use Translatable;

    protected $table = 'oil_what_req';

    protected $fillable = [];

    protected $translatable = [
        'what_title', 'what_desc', 'show_title', 'hide_title', 'req_title', 'req_top_text',

        'req_block_2_title', 'req_list_block1_text', 'req_list_block2_text', 'req_list_block3_text', 'req_left_sub',
    ];
}
