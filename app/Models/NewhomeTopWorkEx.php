<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\NewhomeTopWorkEx
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $image
 * @property string|null $title
 * @property string|null $link
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|NewhomeTopWorkEx newModelQuery()
 * @method static Builder|NewhomeTopWorkEx newQuery()
 * @method static Builder|NewhomeTopWorkEx query()
 * @method static Builder|NewhomeTopWorkEx whereCreatedAt($value)
 * @method static Builder|NewhomeTopWorkEx whereId($value)
 * @method static Builder|NewhomeTopWorkEx whereImage($value)
 * @method static Builder|NewhomeTopWorkEx whereLink($value)
 * @method static Builder|NewhomeTopWorkEx whereOrder($value)
 * @method static Builder|NewhomeTopWorkEx whereTitle($value)
 * @method static Builder|NewhomeTopWorkEx whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|NewhomeTopWorkEx whereUpdatedAt($value)
 * @method static Builder|NewhomeTopWorkEx withTranslation($locale = null, $fallback = true)
 * @method static Builder|NewhomeTopWorkEx withTranslations($locales = null, $fallback = true)
 */
class NewhomeTopWorkEx extends Model
{
    use Translatable;

    protected $table = 'newhome_top_work_ex';
    protected $translatable = ['title', 'link'];
}
