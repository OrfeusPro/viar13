<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\NewhomeService
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $name
 * @property string|null $image
 * @property string|null $title
 * @property string|null $desc
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $link
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|NewhomeService newModelQuery()
 * @method static Builder|NewhomeService newQuery()
 * @method static Builder|NewhomeService query()
 * @method static Builder|NewhomeService whereCreatedAt($value)
 * @method static Builder|NewhomeService whereDesc($value)
 * @method static Builder|NewhomeService whereId($value)
 * @method static Builder|NewhomeService whereImage($value)
 * @method static Builder|NewhomeService whereLink($value)
 * @method static Builder|NewhomeService whereName($value)
 * @method static Builder|NewhomeService whereOrder($value)
 * @method static Builder|NewhomeService whereTitle($value)
 * @method static Builder|NewhomeService whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|NewhomeService whereUpdatedAt($value)
 * @method static Builder|NewhomeService withTranslation($locale = null, $fallback = true)
 * @method static Builder|NewhomeService withTranslations($locales = null, $fallback = true)
 */
class NewhomeService extends Model
{
    use Translatable;
    use Resizable;

    protected $table = 'newhome_services';

    protected $fillable = [];

    protected $translatable = ['name', 'title', 'desc', 'link'];
}
