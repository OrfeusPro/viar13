<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\PageFaq
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $question
 * @property string|null $answer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|PageFaq newModelQuery()
 * @method static Builder|PageFaq newQuery()
 * @method static Builder|PageFaq query()
 * @method static Builder|PageFaq whereAnswer($value)
 * @method static Builder|PageFaq whereCreatedAt($value)
 * @method static Builder|PageFaq whereId($value)
 * @method static Builder|PageFaq whereQuestion($value)
 * @method static Builder|PageFaq whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|PageFaq whereUpdatedAt($value)
 * @method static Builder|PageFaq withTranslation($locale = null, $fallback = true)
 * @method static Builder|PageFaq withTranslations($locales = null, $fallback = true)
 */
class PageFaq extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $table = 'page_faq';

    protected $translatable = ['question', 'answer'];
}
