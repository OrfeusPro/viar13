<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\HeaderMenuItem
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|HeaderMenuItem newModelQuery()
 * @method static Builder|HeaderMenuItem newQuery()
 * @method static Builder|HeaderMenuItem query()
 * @method static Builder|HeaderMenuItem whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|HeaderMenuItem withTranslation($locale = null, $fallback = true)
 * @method static Builder|HeaderMenuItem withTranslations($locales = null, $fallback = true)
 */
class HeaderMenuItem extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $translatable = ['name', 'link'];
}
