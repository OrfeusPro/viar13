<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\SliderMod
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
 * @method static Builder|SliderMod newModelQuery()
 * @method static Builder|SliderMod newQuery()
 * @method static Builder|SliderMod query()
 * @method static Builder|SliderMod whereCreatedAt($value)
 * @method static Builder|SliderMod whereId($value)
 * @method static Builder|SliderMod whereImage($value)
 * @method static Builder|SliderMod whereLink($value)
 * @method static Builder|SliderMod whereText($value)
 * @method static Builder|SliderMod whereTitle($value)
 * @method static Builder|SliderMod whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|SliderMod whereUpdatedAt($value)
 * @method static Builder|SliderMod withTranslation($locale = null, $fallback = true)
 * @method static Builder|SliderMod withTranslations($locales = null, $fallback = true)
 */
class SliderMod extends Model
{
    use Translatable;

    protected $table = 'slider_mod';
    protected $translatable = ['title', 'text', 'link', 'sub_cat_title','sub_cat_text',"sub_cat_link", "main_category_text"];
}
