<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\AllStylesPage
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $title
 * @property string|null $image
 * @property string|null $text
 * @property string|null $price
 * @property string|null $link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $order
 * @property string|null $is_photo
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|AllStylesPage newModelQuery()
 * @method static Builder|AllStylesPage newQuery()
 * @method static Builder|AllStylesPage query()
 * @method static Builder|AllStylesPage whereCreatedAt($value)
 * @method static Builder|AllStylesPage whereId($value)
 * @method static Builder|AllStylesPage whereImage($value)
 * @method static Builder|AllStylesPage whereIsPhoto($value)
 * @method static Builder|AllStylesPage whereLink($value)
 * @method static Builder|AllStylesPage whereOrder($value)
 * @method static Builder|AllStylesPage wherePrice($value)
 * @method static Builder|AllStylesPage whereText($value)
 * @method static Builder|AllStylesPage whereTitle($value)
 * @method static Builder|AllStylesPage whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|AllStylesPage whereUpdatedAt($value)
 * @method static Builder|AllStylesPage withTranslation($locale = null, $fallback = true)
 * @method static Builder|AllStylesPage withTranslations($locales = null, $fallback = true)
 */
class AllStylesPage extends Model
{
    use Translatable;

    protected $table = 'all_styles_page';

    protected $translatable = ['title', 'link', 'text'];
}
