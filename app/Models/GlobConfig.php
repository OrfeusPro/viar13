<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\GlobConfig
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $admin_email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $analytics
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GlobConfig newModelQuery()
 * @method static Builder|GlobConfig newQuery()
 * @method static Builder|GlobConfig query()
 * @method static Builder|GlobConfig whereAdminEmail($value)
 * @method static Builder|GlobConfig whereAnalytics($value)
 * @method static Builder|GlobConfig whereCreatedAt($value)
 * @method static Builder|GlobConfig whereId($value)
 * @method static Builder|GlobConfig whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GlobConfig whereUpdatedAt($value)
 * @method static Builder|GlobConfig withTranslation($locale = null, $fallback = true)
 * @method static Builder|GlobConfig withTranslations($locales = null, $fallback = true)
 */
class GlobConfig extends Model
{
    use Translatable;

    protected $fillable = [];
    protected $table = 'glob_config';

    protected $translatable = ['admin_email'];
}
