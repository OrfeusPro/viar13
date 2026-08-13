<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\ModPhotoPage
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
 * @method static Builder|ModPhotoPage newModelQuery()
 * @method static Builder|ModPhotoPage newQuery()
 * @method static Builder|ModPhotoPage query()
 * @method static Builder|ModPhotoPage whereCreatedAt($value)
 * @method static Builder|ModPhotoPage whereId($value)
 * @method static Builder|ModPhotoPage whereMetaDesc($value)
 * @method static Builder|ModPhotoPage whereMetaTitle($value)
 * @method static Builder|ModPhotoPage whereModImg($value)
 * @method static Builder|ModPhotoPage whereModSubtitle($value)
 * @method static Builder|ModPhotoPage whereModTitle($value)
 * @method static Builder|ModPhotoPage whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|ModPhotoPage whereUpdatedAt($value)
 * @method static Builder|ModPhotoPage withTranslation($locale = null, $fallback = true)
 * @method static Builder|ModPhotoPage withTranslations($locales = null, $fallback = true)
 */
class ModPhotoPage extends Model
{
    use Translatable;

    protected $table = 'mod_photo_page';

    protected $fillable = [];

    protected $translatable = ['meta_title', 'meta_desc', 'mod_title', 'mod_subtitle'];
}
