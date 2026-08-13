<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\HomepageOption
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $page_title
 * @property string|null $fb_share_link_main
 * @property string|null $meta_desc
 * @property string|null $all_styles
 * @property string|null $all_sizes
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|HomepageOption newModelQuery()
 * @method static Builder|HomepageOption newQuery()
 * @method static Builder|HomepageOption query()
 * @method static Builder|HomepageOption whereAllSizes($value)
 * @method static Builder|HomepageOption whereAllStyles($value)
 * @method static Builder|HomepageOption whereCreatedAt($value)
 * @method static Builder|HomepageOption whereFbShareLinkMain($value)
 * @method static Builder|HomepageOption whereId($value)
 * @method static Builder|HomepageOption whereMetaDesc($value)
 * @method static Builder|HomepageOption wherePageTitle($value)
 * @method static Builder|HomepageOption whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|HomepageOption whereUpdatedAt($value)
 * @method static Builder|HomepageOption withTranslation($locale = null, $fallback = true)
 * @method static Builder|HomepageOption withTranslations($locales = null, $fallback = true)
 */
class HomepageOption extends Model
{
    use Translatable;

    protected $fillable = [];

    protected $translatable = [

        'page_title', 'all_styles',

        'gal_title', 'gal_sub_title', 'gal_text', 'gal_link_text',

        'gp_title', 'gp_text', 'gp_link_text',

        'st_title', 'st_sub_title', 'st_text', 'st_link_text',

        'cart_masl_title', 'cart_masl_sub_tiitle', 'cart_masl_link_text', 'cart_masl_text',

        'fb_share_link_main', 'meta_desc', 'h1_text', 'seo', 'seo_city_title', 'seo_city_desc'

    ];
}
