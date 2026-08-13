<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\GalleryDecoration
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string $name
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $coef_sm
 * @property float|null $coef_md
 * @property float|null $coef_lg
 * @property string|null $hint
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GalleryDecoration newModelQuery()
 * @method static Builder|GalleryDecoration newQuery()
 * @method static Builder|GalleryDecoration query()
 * @method static Builder|GalleryDecoration whereCoefLg($value)
 * @method static Builder|GalleryDecoration whereCoefMd($value)
 * @method static Builder|GalleryDecoration whereCoefSm($value)
 * @method static Builder|GalleryDecoration whereCreatedAt($value)
 * @method static Builder|GalleryDecoration whereHint($value)
 * @method static Builder|GalleryDecoration whereId($value)
 * @method static Builder|GalleryDecoration whereName($value)
 * @method static Builder|GalleryDecoration wherePrice($value)
 * @method static Builder|GalleryDecoration whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GalleryDecoration whereUpdatedAt($value)
 * @method static Builder|GalleryDecoration withTranslation($locale = null, $fallback = true)
 * @method static Builder|GalleryDecoration withTranslations($locales = null, $fallback = true)
 */
class GalleryDecoration extends Model
{
    use Translatable;

    // Мазки: P0 - нету, P1 - мазки маслом, P2 - полностью маслом.
    const BRUSHSTROKES_IMG_TAGS = [
        2 => 'P1'
    ];

    // Лак: L0 - нету, L1 - Dammar varnish, L2 - Art gel.
    const LAC_IMG_TAGS = [
        1 => 'L2',
        3 => 'L1'
    ];

    protected $fillable = [];

    protected $translatable = ['name', 'hint'];
}
