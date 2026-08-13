<?php

namespace App\Models;

use App;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;
use App\Models\CanvasRamsColor;
use App\Models\CanvasRamsMaterial;

/**
 * App\Models\CanvasRam
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $img
 * @property string|null $name
 * @property string|null $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $img_bg
 * @property string|null $css_params
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CanvasRam newModelQuery()
 * @method static Builder|CanvasRam newQuery()
 * @method static Builder|CanvasRam query()
 * @method static Builder|CanvasRam whereCreatedAt($value)
 * @method static Builder|CanvasRam whereCssParams($value)
 * @method static Builder|CanvasRam whereId($value)
 * @method static Builder|CanvasRam whereImg($value)
 * @method static Builder|CanvasRam whereImgBg($value)
 * @method static Builder|CanvasRam whereName($value)
 * @method static Builder|CanvasRam wherePrice($value)
 * @method static Builder|CanvasRam whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CanvasRam whereUpdatedAt($value)
 * @method static Builder|CanvasRam withTranslation($locale = null, $fallback = true)
 * @method static Builder|CanvasRam withTranslations($locales = null, $fallback = true)
 */
class CanvasRam extends Model
{
    use Translatable;

    const TYPE_BAGUETTE  = 'baguette';
    const TYPE_DEFAULT = 'default';
    const TYPE_PAPER = 'paper';
    const TYPE_PAPER_PREMIUM = 'paper_premium';

    protected $translatable = ['name'];

    public static function isFramedOption($id): bool
    {
        $ramId = (int) $id;
        if ($ramId <= 0) {
            return false;
        }

        $ram = CanvasRam::query()->where('id', $ramId)->first(['price']);
        if (!$ram) {
            return false;
        }

        return (float) $ram->price > 0;
    }

    /**
     * Production filename flag for a selected frame.
     * Paper frames are distinguished from other paid frames without relying on
     * their translated names, so the result is identical in every locale.
     */
    public static function productionBagetCode($id): string
    {
        $ramId = (int) $id;
        if ($ramId <= 0) {
            return 'B0';
        }

        $ram = CanvasRam::query()->where('id', $ramId)->first(['price', 'type']);
        if (!$ram || (float) $ram->price <= 0) {
            return 'B0';
        }

        return in_array($ram->type, [self::TYPE_PAPER, self::TYPE_PAPER_PREMIUM], true)
            ? 'B2'
            : 'B1';
    }

    public static function getRamNameById($id)
    {
        return CanvasRam::where('id', $id)->get()->translate(App::getLocale(), 'ru')->pluck('name')->first();
    }

    public function color()
    {
        return $this->hasMany(CanvasRamsColor::class, "id", "color_id");
    }

    public function material()
    {
        return $this->hasMany(CanvasRamsMaterial::class, "id", "material_id");
    }

}
