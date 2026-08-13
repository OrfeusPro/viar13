<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\Blog
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $title
 * @property string|null $desc
 * @property string|null $read_more
 * @property string|null $int_ideas
 * @property string|null $load_more
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $fb_link
 * @property string|null $ig_link
 * @property string|null $od_link
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|Blog newModelQuery()
 * @method static Builder|Blog newQuery()
 * @method static Builder|Blog query()
 * @method static Builder|Blog whereCreatedAt($value)
 * @method static Builder|Blog whereDesc($value)
 * @method static Builder|Blog whereFbLink($value)
 * @method static Builder|Blog whereId($value)
 * @method static Builder|Blog whereIgLink($value)
 * @method static Builder|Blog whereIntIdeas($value)
 * @method static Builder|Blog whereLoadMore($value)
 * @method static Builder|Blog whereMetaTitle($value)
 * @method static Builder|Blog whereOdLink($value)
 * @method static Builder|Blog whereReadMore($value)
 * @method static Builder|Blog whereTitle($value)
 * @method static Builder|Blog whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|Blog whereUpdatedAt($value)
 * @method static Builder|Blog withTranslation($locale = null, $fallback = true)
 * @method static Builder|Blog withTranslations($locales = null, $fallback = true)
 */
class Blog extends Model
{
    use Translatable;

    protected $table = 'blog';
    protected $fillable = [];

    protected $translatable = [
        'meta_title', 'meta_desc', 'title', 'desc', 'read_more', 'int_ideas', 'load_more', 'fb_link', 'ig_link', 'od_link', 'seo', 'seo_tag_all', 'seo_tag_all_meta_title', 'seo_tag_all_meta_desc'
    ];
}
