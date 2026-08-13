<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\NewhomeWorkEx
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $image
 * @property string|null $title
 * @property string|null $size
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|NewhomeWorkEx newModelQuery()
 * @method static Builder|NewhomeWorkEx newQuery()
 * @method static Builder|NewhomeWorkEx query()
 * @method static Builder|NewhomeWorkEx whereCreatedAt($value)
 * @method static Builder|NewhomeWorkEx whereId($value)
 * @method static Builder|NewhomeWorkEx whereImage($value)
 * @method static Builder|NewhomeWorkEx whereOrder($value)
 * @method static Builder|NewhomeWorkEx whereSize($value)
 * @method static Builder|NewhomeWorkEx whereTitle($value)
 * @method static Builder|NewhomeWorkEx whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|NewhomeWorkEx whereUpdatedAt($value)
 * @method static Builder|NewhomeWorkEx withTranslation($locale = null, $fallback = true)
 * @method static Builder|NewhomeWorkEx withTranslations($locales = null, $fallback = true)
 */
class NewhomeWorkEx extends Model
{
    use Translatable;

    protected $table = 'newhome_work_ex';
    protected $translatable = ['title', 'size'];
}
