<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\GalleryFiltersPage
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $sub_cats_title
 * @property string|null $holl_type
 * @property string|null $color_select
 * @property string|null $find_btn_text
 * @property string|null $art_jenre_text
 * @property string|null $art_style_text
 * @property string|null $art_size_text
 * @property string|null $art_price_text
 * @property string|null $art_price_from_text
 * @property string|null $art_order_text
 * @property string|null $popular_text
 * @property string|null $recomm_text
 * @property string|null $all_cats_arts_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GalleryFiltersPage newModelQuery()
 * @method static Builder|GalleryFiltersPage newQuery()
 * @method static Builder|GalleryFiltersPage query()
 * @method static Builder|GalleryFiltersPage whereAllCatsArtsText($value)
 * @method static Builder|GalleryFiltersPage whereArtJenreText($value)
 * @method static Builder|GalleryFiltersPage whereArtOrderText($value)
 * @method static Builder|GalleryFiltersPage whereArtPriceFromText($value)
 * @method static Builder|GalleryFiltersPage whereArtPriceText($value)
 * @method static Builder|GalleryFiltersPage whereArtSizeText($value)
 * @method static Builder|GalleryFiltersPage whereArtStyleText($value)
 * @method static Builder|GalleryFiltersPage whereColorSelect($value)
 * @method static Builder|GalleryFiltersPage whereCreatedAt($value)
 * @method static Builder|GalleryFiltersPage whereFindBtnText($value)
 * @method static Builder|GalleryFiltersPage whereHollType($value)
 * @method static Builder|GalleryFiltersPage whereId($value)
 * @method static Builder|GalleryFiltersPage wherePopularText($value)
 * @method static Builder|GalleryFiltersPage whereRecommText($value)
 * @method static Builder|GalleryFiltersPage whereSubCatsTitle($value)
 * @method static Builder|GalleryFiltersPage whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GalleryFiltersPage whereUpdatedAt($value)
 * @method static Builder|GalleryFiltersPage withTranslation($locale = null, $fallback = true)
 * @method static Builder|GalleryFiltersPage withTranslations($locales = null, $fallback = true)
 */
class GalleryFiltersPage extends Model
{
    use Translatable;

    protected $table = 'gallery_filters_page';

    protected $fillable = [];

    protected $translatable = [
        'sub_cats_title', 'holl_type', 'color_select', 'find_btn_text', 'art_jenre_text', 'art_style_text',

        'art_size_text', 'art_price_text', 'art_price_from_text', 'art_order_text', 'popular_text', 'recomm_text',
        'all_cats_arts_text',
    ];
}
