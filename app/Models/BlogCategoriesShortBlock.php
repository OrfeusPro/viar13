<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\BlogPost
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $meta_desc
 * @property string|null $title
 * @property string|null $text
 * @property int|null $is_idea
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $slug
 * @property string|null $image
 * @property string|null $meta_robots
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|BlogPost newModelQuery()
 * @method static Builder|BlogPost newQuery()
 * @method static Builder|BlogPost query()
 * @method static Builder|BlogPost whereCreatedAt($value)
 * @method static Builder|BlogPost whereId($value)
 * @method static Builder|BlogPost whereImage($value)
 * @method static Builder|BlogPost whereIsIdea($value)
 * @method static Builder|BlogPost whereMetaDesc($value)
 * @method static Builder|BlogPost whereMetaRobots($value)
 * @method static Builder|BlogPost whereMetaTitle($value)
 * @method static Builder|BlogPost whereSlug($value)
 * @method static Builder|BlogPost whereText($value)
 * @method static Builder|BlogPost whereTitle($value)
 * @method static Builder|BlogPost whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|BlogPost whereUpdatedAt($value)
 * @method static Builder|BlogPost withTranslation($locale = null, $fallback = true)
 * @method static Builder|BlogPost withTranslations($locales = null, $fallback = true)
 */
class BlogCategoriesShortBlock extends Model
{
    use Translatable;

    protected $table = 'blog_categories_short_blocks';
    protected $fillable = [];
    protected $translatable = ['title', 'text_top', 'text_right', 'text_bottom'];

    public function categoryes()
    {
        return $this->belongsToMany(BlogCategory::class);
    }
}
