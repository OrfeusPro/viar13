<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\GalleryTagRoom
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GalleryTagRoom newModelQuery()
 * @method static Builder|GalleryTagRoom newQuery()
 * @method static Builder|GalleryTagRoom query()
 * @method static Builder|GalleryTagRoom whereCreatedAt($value)
 * @method static Builder|GalleryTagRoom whereId($value)
 * @method static Builder|GalleryTagRoom whereName($value)
 * @method static Builder|GalleryTagRoom whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GalleryTagRoom whereUpdatedAt($value)
 * @method static Builder|GalleryTagRoom withTranslation($locale = null, $fallback = true)
 * @method static Builder|GalleryTagRoom withTranslations($locales = null, $fallback = true)
 */
class GalleryTagRoom extends Model
{
    use Translatable;

    protected $fillable = [

        'id', 'name',

    ];

    protected $translatable = ['name'];

    public function getTagForCategory(array $id)
    {
        return GalleryTagRoom::get();
    }
}
