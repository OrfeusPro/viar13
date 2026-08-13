<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\SliderPhoto
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $title
 * @property string|null $text
 * @property string|null $link
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|SliderPhoto newModelQuery()
 * @method static Builder|SliderPhoto newQuery()
 * @method static Builder|SliderPhoto query()
 * @method static Builder|SliderPhoto whereCreatedAt($value)
 * @method static Builder|SliderPhoto whereId($value)
 * @method static Builder|SliderPhoto whereImage($value)
 * @method static Builder|SliderPhoto whereLink($value)
 * @method static Builder|SliderPhoto whereText($value)
 * @method static Builder|SliderPhoto whereTitle($value)
 * @method static Builder|SliderPhoto whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|SliderPhoto whereUpdatedAt($value)
 * @method static Builder|SliderPhoto withTranslation($locale = null, $fallback = true)
 * @method static Builder|SliderPhoto withTranslations($locales = null, $fallback = true)
 */
class SliderPhoto extends Model
{
    use Translatable;

    protected $table = 'slider_photo';
    protected $translatable = ['title', 'text', 'link', 'sub_cat_title','sub_cat_text',"sub_cat_link", "main_category_text"];
}
