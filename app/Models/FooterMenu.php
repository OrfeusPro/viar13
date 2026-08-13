<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\FooterMenu
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $title
 * @property string|null $link
 * @property int|null $menu_pos
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|FooterMenu newModelQuery()
 * @method static Builder|FooterMenu newQuery()
 * @method static Builder|FooterMenu query()
 * @method static Builder|FooterMenu whereCreatedAt($value)
 * @method static Builder|FooterMenu whereId($value)
 * @method static Builder|FooterMenu whereLink($value)
 * @method static Builder|FooterMenu whereMenuPos($value)
 * @method static Builder|FooterMenu whereOrder($value)
 * @method static Builder|FooterMenu whereTitle($value)
 * @method static Builder|FooterMenu whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|FooterMenu whereUpdatedAt($value)
 * @method static Builder|FooterMenu withTranslation($locale = null, $fallback = true)
 * @method static Builder|FooterMenu withTranslations($locales = null, $fallback = true)
 */
class FooterMenu extends Model
{
    use Translatable;

    protected $table = 'footer_menu';
    protected $translatable = ['title', 'link'];
}
