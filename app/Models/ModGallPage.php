<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\ModGallPage
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
 * @method static Builder|ModGallPage newModelQuery()
 * @method static Builder|ModGallPage newQuery()
 * @method static Builder|ModGallPage query()
 * @method static Builder|ModGallPage whereCreatedAt($value)
 * @method static Builder|ModGallPage whereId($value)
 * @method static Builder|ModGallPage whereMetaDesc($value)
 * @method static Builder|ModGallPage whereMetaTitle($value)
 * @method static Builder|ModGallPage whereModImg($value)
 * @method static Builder|ModGallPage whereModSubtitle($value)
 * @method static Builder|ModGallPage whereModTitle($value)
 * @method static Builder|ModGallPage whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|ModGallPage whereUpdatedAt($value)
 * @method static Builder|ModGallPage withTranslation($locale = null, $fallback = true)
 * @method static Builder|ModGallPage withTranslations($locales = null, $fallback = true)
 */
class ModGallPage extends Model
{
    use Translatable;

    protected $table = 'mod_gall_page';

    protected $fillable = [];

    protected $translatable = ['meta_title', 'meta_desc', 'mod_title', 'mod_subtitle'];
}
