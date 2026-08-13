<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\GraphPortPage
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GraphPortPage newModelQuery()
 * @method static Builder|GraphPortPage newQuery()
 * @method static Builder|GraphPortPage query()
 * @method static Builder|GraphPortPage whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GraphPortPage withTranslation($locale = null, $fallback = true)
 * @method static Builder|GraphPortPage withTranslations($locales = null, $fallback = true)
 */
class GraphPortPage extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $table = 'graph_port_pages';

    protected $translatable = [
        'meta_title', 'meta_desc', 'title', 'sub_title', 'description',

        'right_title', 'right_sub_title',
    ];
}
