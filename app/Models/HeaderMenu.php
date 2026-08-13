<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\HeaderMenu
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $title
 * @property string|null $link
 * @property string|null $images
 * @property int|null $menu_pos
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|HeaderMenu newModelQuery()
 * @method static Builder|HeaderMenu newQuery()
 * @method static Builder|HeaderMenu query()
 * @method static Builder|HeaderMenu whereCreatedAt($value)
 * @method static Builder|HeaderMenu whereId($value)
 * @method static Builder|HeaderMenu whereImages($value)
 * @method static Builder|HeaderMenu whereLink($value)
 * @method static Builder|HeaderMenu whereMenuPos($value)
 * @method static Builder|HeaderMenu whereOrder($value)
 * @method static Builder|HeaderMenu whereTitle($value)
 * @method static Builder|HeaderMenu whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|HeaderMenu whereUpdatedAt($value)
 * @method static Builder|HeaderMenu withTranslation($locale = null, $fallback = true)
 * @method static Builder|HeaderMenu withTranslations($locales = null, $fallback = true)
 */
class HeaderMenu extends Model
{
    use Translatable;

    protected $table = 'header_menu';

    protected $translatable = ['title', 'link'];
}
