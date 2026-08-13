<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\ModReprPage
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $meta_desc
 * @property string|null $mod_title
 * @property string|null $mod_subtitle
 * @property string|null $mod_img
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|ModReprPage newModelQuery()
 * @method static Builder|ModReprPage newQuery()
 * @method static Builder|ModReprPage query()
 * @method static Builder|ModReprPage whereCreatedAt($value)
 * @method static Builder|ModReprPage whereId($value)
 * @method static Builder|ModReprPage whereMetaDesc($value)
 * @method static Builder|ModReprPage whereMetaTitle($value)
 * @method static Builder|ModReprPage whereModImg($value)
 * @method static Builder|ModReprPage whereModSubtitle($value)
 * @method static Builder|ModReprPage whereModTitle($value)
 * @method static Builder|ModReprPage whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|ModReprPage whereUpdatedAt($value)
 * @method static Builder|ModReprPage withTranslation($locale = null, $fallback = true)
 * @method static Builder|ModReprPage withTranslations($locales = null, $fallback = true)
 */
class ModReprPage extends Model
{
    use Translatable;

    protected $table = 'mod_repr_page';

    protected $fillable = [];

    protected $translatable = ['meta_title', 'meta_desc', 'mod_title', 'mod_subtitle'];
}
