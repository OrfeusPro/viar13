<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\SharjWorkGroupEx
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
 * @method static Builder|SharjWorkGroupEx newModelQuery()
 * @method static Builder|SharjWorkGroupEx newQuery()
 * @method static Builder|SharjWorkGroupEx query()
 * @method static Builder|SharjWorkGroupEx whereCreatedAt($value)
 * @method static Builder|SharjWorkGroupEx whereId($value)
 * @method static Builder|SharjWorkGroupEx whereImage($value)
 * @method static Builder|SharjWorkGroupEx whereName($value)
 * @method static Builder|SharjWorkGroupEx whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|SharjWorkGroupEx whereUpdatedAt($value)
 * @method static Builder|SharjWorkGroupEx withTranslation($locale = null, $fallback = true)
 * @method static Builder|SharjWorkGroupEx withTranslations($locales = null, $fallback = true)
 */
class SharjWorkGroupEx extends Model
{
    use Translatable;

    protected $table = 'sharj_work_group_ex';
    protected $fillable = [];

    protected $translatable = ['name'];
}
