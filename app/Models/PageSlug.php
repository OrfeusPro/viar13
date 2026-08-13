<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\PageSlug
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $canvas_slug
 * @property string|null $collage_slug
 * @property string|null $modular_slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|PageSlug newModelQuery()
 * @method static Builder|PageSlug newQuery()
 * @method static Builder|PageSlug query()
 * @method static Builder|PageSlug whereCanvasSlug($value)
 * @method static Builder|PageSlug whereCollageSlug($value)
 * @method static Builder|PageSlug whereCreatedAt($value)
 * @method static Builder|PageSlug whereId($value)
 * @method static Builder|PageSlug whereModularSlug($value)
 * @method static Builder|PageSlug whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|PageSlug whereUpdatedAt($value)
 * @method static Builder|PageSlug withTranslation($locale = null, $fallback = true)
 * @method static Builder|PageSlug withTranslations($locales = null, $fallback = true)
 */
class PageSlug extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $table = 'page_slugs';

    protected $translatable = ['canvas_slug', 'collage_slug', 'modular_slug', 'family_slug'];
}
