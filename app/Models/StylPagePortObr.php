<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\StylPagePortObr
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
 * @method static Builder|StylPagePortObr newModelQuery()
 * @method static Builder|StylPagePortObr newQuery()
 * @method static Builder|StylPagePortObr query()
 * @method static Builder|StylPagePortObr whereCreatedAt($value)
 * @method static Builder|StylPagePortObr whereId($value)
 * @method static Builder|StylPagePortObr whereImage($value)
 * @method static Builder|StylPagePortObr whereName($value)
 * @method static Builder|StylPagePortObr whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|StylPagePortObr whereUpdatedAt($value)
 * @method static Builder|StylPagePortObr withTranslation($locale = null, $fallback = true)
 * @method static Builder|StylPagePortObr withTranslations($locales = null, $fallback = true)
 */
class StylPagePortObr extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $table = 'styl_page_port_obr';

    protected $translatable = ['name'];
}
