<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\PortPrevItem
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
 * @method static Builder|PortPrevItem newModelQuery()
 * @method static Builder|PortPrevItem newQuery()
 * @method static Builder|PortPrevItem query()
 * @method static Builder|PortPrevItem whereCreatedAt($value)
 * @method static Builder|PortPrevItem whereId($value)
 * @method static Builder|PortPrevItem whereImage($value)
 * @method static Builder|PortPrevItem whereName($value)
 * @method static Builder|PortPrevItem whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|PortPrevItem whereUpdatedAt($value)
 * @method static Builder|PortPrevItem withTranslation($locale = null, $fallback = true)
 * @method static Builder|PortPrevItem withTranslations($locales = null, $fallback = true)
 */
class PortPrevItem extends Model
{
    use Translatable;

    protected $table = 'port_prev_items';
    protected $fillable = [];

    protected $translatable = ['name'];
}
