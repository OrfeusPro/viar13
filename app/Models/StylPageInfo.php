<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\StylPageInfo
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $meta_desc
 * @property string|null $port_obr_title
 * @property string|null $port_obr_text
 * @property string|null $all_obr_link
 * @property string|null $all_obr_link_text
 * @property string|null $group_obr_title
 * @property string|null $group_obr_text
 * @property string|null $group_obr_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|StylPageInfo newModelQuery()
 * @method static Builder|StylPageInfo newQuery()
 * @method static Builder|StylPageInfo query()
 * @method static Builder|StylPageInfo whereAllObrLink($value)
 * @method static Builder|StylPageInfo whereAllObrLinkText($value)
 * @method static Builder|StylPageInfo whereCreatedAt($value)
 * @method static Builder|StylPageInfo whereGroupObrLink($value)
 * @method static Builder|StylPageInfo whereGroupObrText($value)
 * @method static Builder|StylPageInfo whereGroupObrTitle($value)
 * @method static Builder|StylPageInfo whereId($value)
 * @method static Builder|StylPageInfo whereMetaDesc($value)
 * @method static Builder|StylPageInfo whereMetaTitle($value)
 * @method static Builder|StylPageInfo wherePortObrText($value)
 * @method static Builder|StylPageInfo wherePortObrTitle($value)
 * @method static Builder|StylPageInfo whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|StylPageInfo whereUpdatedAt($value)
 * @method static Builder|StylPageInfo withTranslation($locale = null, $fallback = true)
 * @method static Builder|StylPageInfo withTranslations($locales = null, $fallback = true)
 */
class StylPageInfo extends Model
{
    use Translatable;

    protected $table = 'styl_page_info';

    protected $fillable = [];

    protected $translatable = [
        'meta_title', 'meta_desc',

        'port_obr_title', 'port_obr_text', 'all_obr_link', 'all_obr_link_text',

        'group_obr_title', 'group_obr_text', 'group_obr_link',
    ];
}
