<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\AllStyle
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $meta_desc
 * @property string|null $title
 * @property string|null $desc
 * @property string|null $order_btn_text
 * @property string|null $from_text
 * @property string|null $show_more_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $price_val
 * @property string|null $f_title
 * @property string|null $f_desc1
 * @property string|null $f_desc2
 * @property string|null $f_email
 * @property string|null $f_phone
 * @property string|null $f_send
 * @property string|null $f_success
 * @property string|null $files_loaded
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|AllStyle newModelQuery()
 * @method static Builder|AllStyle newQuery()
 * @method static Builder|AllStyle query()
 * @method static Builder|AllStyle whereCreatedAt($value)
 * @method static Builder|AllStyle whereDesc($value)
 * @method static Builder|AllStyle whereFDesc1($value)
 * @method static Builder|AllStyle whereFDesc2($value)
 * @method static Builder|AllStyle whereFEmail($value)
 * @method static Builder|AllStyle whereFPhone($value)
 * @method static Builder|AllStyle whereFSend($value)
 * @method static Builder|AllStyle whereFSuccess($value)
 * @method static Builder|AllStyle whereFTitle($value)
 * @method static Builder|AllStyle whereFilesLoaded($value)
 * @method static Builder|AllStyle whereFromText($value)
 * @method static Builder|AllStyle whereId($value)
 * @method static Builder|AllStyle whereMetaDesc($value)
 * @method static Builder|AllStyle whereMetaTitle($value)
 * @method static Builder|AllStyle whereOrderBtnText($value)
 * @method static Builder|AllStyle wherePriceVal($value)
 * @method static Builder|AllStyle whereShowMoreText($value)
 * @method static Builder|AllStyle whereTitle($value)
 * @method static Builder|AllStyle whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|AllStyle whereUpdatedAt($value)
 * @method static Builder|AllStyle withTranslation($locale = null, $fallback = true)
 * @method static Builder|AllStyle withTranslations($locales = null, $fallback = true)
 */
class AllStyle extends Model
{
    use Translatable;

    protected $table = 'all_styles';
    protected $translatable = [
        'meta_title', 'meta_desc', 'title', 'desc', 'order_btn_text', 'from_text', 'show_more_text', 'price_val',
        'f_title', 'f_desc1', 'f_desc2', 'f_email', 'f_phone', 'f_send', 'f_success', 'files_loaded'
    ];
}
