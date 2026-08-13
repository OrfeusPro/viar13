<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\StylPagePortGoupObr
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $name
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|StylPagePortGoupObr newModelQuery()
 * @method static Builder|StylPagePortGoupObr newQuery()
 * @method static Builder|StylPagePortGoupObr query()
 * @method static Builder|StylPagePortGoupObr whereCreatedAt($value)
 * @method static Builder|StylPagePortGoupObr whereId($value)
 * @method static Builder|StylPagePortGoupObr whereImage($value)
 * @method static Builder|StylPagePortGoupObr whereName($value)
 * @method static Builder|StylPagePortGoupObr whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|StylPagePortGoupObr whereUpdatedAt($value)
 * @method static Builder|StylPagePortGoupObr withTranslation($locale = null, $fallback = true)
 * @method static Builder|StylPagePortGoupObr withTranslations($locales = null, $fallback = true)
 */
class StylPagePortGoupObr extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $table = 'styl_page_port_goup_obr';

    protected $translatable = ['name'];
}
