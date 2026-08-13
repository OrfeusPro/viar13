<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\PortraitsHard
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $name
 * @property string|null $img1
 * @property string|null $text
 * @property string|null $img2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|PortraitsHard newModelQuery()
 * @method static Builder|PortraitsHard newQuery()
 * @method static Builder|PortraitsHard query()
 * @method static Builder|PortraitsHard whereCreatedAt($value)
 * @method static Builder|PortraitsHard whereId($value)
 * @method static Builder|PortraitsHard whereImg1($value)
 * @method static Builder|PortraitsHard whereImg2($value)
 * @method static Builder|PortraitsHard whereName($value)
 * @method static Builder|PortraitsHard whereText($value)
 * @method static Builder|PortraitsHard whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|PortraitsHard whereUpdatedAt($value)
 * @method static Builder|PortraitsHard withTranslation($locale = null, $fallback = true)
 * @method static Builder|PortraitsHard withTranslations($locales = null, $fallback = true)
 */
class PortraitsHard extends Model
{
    use Translatable;

    protected $table = 'portraits_hard';
    protected $fillable = [];

    protected $translatable = ['name', 'text'];
}
