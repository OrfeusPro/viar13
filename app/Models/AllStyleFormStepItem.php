<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\AllStyleFormStepItem
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $name
 * @property string|null $tooltip
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $step
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|AllStyleFormStepItem newModelQuery()
 * @method static Builder|AllStyleFormStepItem newQuery()
 * @method static Builder|AllStyleFormStepItem query()
 * @method static Builder|AllStyleFormStepItem whereCreatedAt($value)
 * @method static Builder|AllStyleFormStepItem whereId($value)
 * @method static Builder|AllStyleFormStepItem whereImage($value)
 * @method static Builder|AllStyleFormStepItem whereName($value)
 * @method static Builder|AllStyleFormStepItem whereStep($value)
 * @method static Builder|AllStyleFormStepItem whereTooltip($value)
 * @method static Builder|AllStyleFormStepItem whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|AllStyleFormStepItem whereUpdatedAt($value)
 * @method static Builder|AllStyleFormStepItem withTranslation($locale = null, $fallback = true)
 * @method static Builder|AllStyleFormStepItem withTranslations($locales = null, $fallback = true)
 */
class AllStyleFormStepItem extends Model
{
    use Translatable;

    protected $translatable = ['name', 'tooltip'];
}
