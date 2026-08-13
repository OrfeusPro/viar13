<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\PageFaqDesc
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $title
 * @property string|null $desc
 * @property string|null $bot_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|PageFaqDesc newModelQuery()
 * @method static Builder|PageFaqDesc newQuery()
 * @method static Builder|PageFaqDesc query()
 * @method static Builder|PageFaqDesc whereBotText($value)
 * @method static Builder|PageFaqDesc whereCreatedAt($value)
 * @method static Builder|PageFaqDesc whereDesc($value)
 * @method static Builder|PageFaqDesc whereId($value)
 * @method static Builder|PageFaqDesc whereTitle($value)
 * @method static Builder|PageFaqDesc whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|PageFaqDesc whereUpdatedAt($value)
 * @method static Builder|PageFaqDesc withTranslation($locale = null, $fallback = true)
 * @method static Builder|PageFaqDesc withTranslations($locales = null, $fallback = true)
 */
class PageFaqDesc extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $table = 'page_faq_desc';

    protected $translatable = ['desc', 'title', 'bot_text'];
}
