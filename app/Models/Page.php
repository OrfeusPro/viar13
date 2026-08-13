<?php

namespace App\Models;

use App\Models\Concerns\HasAltSuggestions;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\Page
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $title
 * @property string|null $meta_description
 * @property string|null $meta_keys
 * @property string|null $url
 * @property string|null $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $meta_title
 * @property string|null $meta_robots
 * @property string|null $template
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|Page newModelQuery()
 * @method static Builder|Page newQuery()
 * @method static Builder|Page query()
 * @method static Builder|Page whereContent($value)
 * @method static Builder|Page whereCreatedAt($value)
 * @method static Builder|Page whereId($value)
 * @method static Builder|Page whereMetaDescription($value)
 * @method static Builder|Page whereMetaKeys($value)
 * @method static Builder|Page whereMetaRobots($value)
 * @method static Builder|Page whereMetaTitle($value)
 * @method static Builder|Page whereTemplate($value)
 * @method static Builder|Page whereTitle($value)
 * @method static Builder|Page whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|Page whereUpdatedAt($value)
 * @method static Builder|Page whereUrl($value)
 * @method static Builder|Page withTranslation($locale = null, $fallback = true)
 * @method static Builder|Page withTranslations($locales = null, $fallback = true)
 */
class Page extends Model
{
    use HasAltSuggestions;
    use Translatable;

    protected $fillable = [

        'title', 'url',

    ];

    protected $translatable = ['meta_description', 'meta_title'];

    protected $hidden = [];

    public function getOne($url)
    {
        return Page::where('url', $url)->first();
    }
}
