<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\CanvasPhotoImprove
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $hint
 * @property string|null $image
 * @property float $price
 * @property int $sort
 * @property bool $is_active
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CanvasPhotoImprove newModelQuery()
 * @method static Builder|CanvasPhotoImprove newQuery()
 * @method static Builder|CanvasPhotoImprove query()
 * @method static Builder|CanvasPhotoImprove whereCode($value)
 * @method static Builder|CanvasPhotoImprove whereCreatedAt($value)
 * @method static Builder|CanvasPhotoImprove whereHint($value)
 * @method static Builder|CanvasPhotoImprove whereId($value)
 * @method static Builder|CanvasPhotoImprove whereImage($value)
 * @method static Builder|CanvasPhotoImprove whereIsActive($value)
 * @method static Builder|CanvasPhotoImprove whereIsDefault($value)
 * @method static Builder|CanvasPhotoImprove whereName($value)
 * @method static Builder|CanvasPhotoImprove wherePrice($value)
 * @method static Builder|CanvasPhotoImprove whereSort($value)
 * @method static Builder|CanvasPhotoImprove whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CanvasPhotoImprove whereUpdatedAt($value)
 * @method static Builder|CanvasPhotoImprove withTranslation($locale = null, $fallback = true)
 * @method static Builder|CanvasPhotoImprove withTranslations($locales = null, $fallback = true)
 */
class CanvasPhotoImprove extends Model
{
    use Translatable;

    protected $table = 'canvas_photo_improvements';

    protected $fillable = [];

    protected $translatable = [
        'name',
        'hint',
    ];
}
