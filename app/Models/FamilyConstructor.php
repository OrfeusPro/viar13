<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\FamilyConstructor
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $title
 * @property string|null $sizes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $meta_desc
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|FamilyConstructor newModelQuery()
 * @method static Builder|FamilyConstructor newQuery()
 * @method static Builder|FamilyConstructor query()
 * @method static Builder|FamilyConstructor whereCreatedAt($value)
 * @method static Builder|FamilyConstructor whereId($value)
 * @method static Builder|FamilyConstructor whereMetaDesc($value)
 * @method static Builder|FamilyConstructor whereMetaTitle($value)
 * @method static Builder|FamilyConstructor whereSizes($value)
 * @method static Builder|FamilyConstructor whereTitle($value)
 * @method static Builder|FamilyConstructor whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|FamilyConstructor whereUpdatedAt($value)
 * @method static Builder|FamilyConstructor withTranslation($locale = null, $fallback = true)
 * @method static Builder|FamilyConstructor withTranslations($locales = null, $fallback = true)
 */
class FamilyConstructor extends Model
{
    use Translatable;

    protected $table = 'family_constructor';

    protected $fillable = [];

    protected $translatable = ['meta_title', 'title'];
}
