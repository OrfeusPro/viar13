<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\SharjWorkEx
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
 * @method static Builder|SharjWorkEx newModelQuery()
 * @method static Builder|SharjWorkEx newQuery()
 * @method static Builder|SharjWorkEx query()
 * @method static Builder|SharjWorkEx whereCreatedAt($value)
 * @method static Builder|SharjWorkEx whereId($value)
 * @method static Builder|SharjWorkEx whereImage($value)
 * @method static Builder|SharjWorkEx whereName($value)
 * @method static Builder|SharjWorkEx whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|SharjWorkEx whereUpdatedAt($value)
 * @method static Builder|SharjWorkEx withTranslation($locale = null, $fallback = true)
 * @method static Builder|SharjWorkEx withTranslations($locales = null, $fallback = true)
 */
class SharjWorkEx extends Model
{
    use Translatable;

    protected $table = 'sharj_work_ex';
    protected $fillable = [];

    protected $translatable = ['name'];
}
