<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\HomepageTopslide
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $text
 * @property string|null $link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|HomepageTopslide newModelQuery()
 * @method static Builder|HomepageTopslide newQuery()
 * @method static Builder|HomepageTopslide query()
 * @method static Builder|HomepageTopslide whereCreatedAt($value)
 * @method static Builder|HomepageTopslide whereId($value)
 * @method static Builder|HomepageTopslide whereLink($value)
 * @method static Builder|HomepageTopslide whereText($value)
 * @method static Builder|HomepageTopslide whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|HomepageTopslide whereUpdatedAt($value)
 * @method static Builder|HomepageTopslide withTranslation($locale = null, $fallback = true)
 * @method static Builder|HomepageTopslide withTranslations($locales = null, $fallback = true)
 */
class HomepageTopslide extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $translatable = ['text', 'link'];
}
