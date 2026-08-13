<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\SliderRepr
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
 * @method static Builder|SliderRepr newModelQuery()
 * @method static Builder|SliderRepr newQuery()
 * @method static Builder|SliderRepr query()
 * @method static Builder|SliderRepr whereCreatedAt($value)
 * @method static Builder|SliderRepr whereId($value)
 * @method static Builder|SliderRepr whereImage($value)
 * @method static Builder|SliderRepr whereLink($value)
 * @method static Builder|SliderRepr whereText($value)
 * @method static Builder|SliderRepr whereTitle($value)
 * @method static Builder|SliderRepr whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|SliderRepr whereUpdatedAt($value)
 * @method static Builder|SliderRepr withTranslation($locale = null, $fallback = true)
 * @method static Builder|SliderRepr withTranslations($locales = null, $fallback = true)
 */
class SliderRepr extends Model
{
    use Translatable;

    protected $table = 'slider_repr';
    protected $translatable = ['title', 'text', 'link', 'sub_cat_title','sub_cat_text',"sub_cat_link", "main_category_text"];
}
