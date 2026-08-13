<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\GalleryPage
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $left_title
 * @property string|null $left_sub_title
 * @property string|null $right_title
 * @property string|null $right_t1
 * @property string|null $right_t2
 * @property string|null $right_t3
 * @property string|null $modc_title
 * @property string|null $modc_sub
 * @property string|null $modc_text
 * @property string|null $modc_link_text
 * @property string|null $fotoc_title
 * @property string|null $fotoc_sub
 * @property string|null $fotoc_text
 * @property string|null $fotoc_link_text
 * @property string|null $repr_title
 * @property string|null $repr_sub_title
 * @property string|null $repr_text
 * @property string|null $repr_link_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $meta_description
 * @property string|null $gal__desc
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GalleryPage newModelQuery()
 * @method static Builder|GalleryPage newQuery()
 * @method static Builder|GalleryPage query()
 * @method static Builder|GalleryPage whereCreatedAt($value)
 * @method static Builder|GalleryPage whereFotocLinkText($value)
 * @method static Builder|GalleryPage whereFotocSub($value)
 * @method static Builder|GalleryPage whereFotocText($value)
 * @method static Builder|GalleryPage whereFotocTitle($value)
 * @method static Builder|GalleryPage whereGalDesc($value)
 * @method static Builder|GalleryPage whereId($value)
 * @method static Builder|GalleryPage whereLeftSubTitle($value)
 * @method static Builder|GalleryPage whereLeftTitle($value)
 * @method static Builder|GalleryPage whereMetaDescription($value)
 * @method static Builder|GalleryPage whereMetaTitle($value)
 * @method static Builder|GalleryPage whereModcLinkText($value)
 * @method static Builder|GalleryPage whereModcSub($value)
 * @method static Builder|GalleryPage whereModcText($value)
 * @method static Builder|GalleryPage whereModcTitle($value)
 * @method static Builder|GalleryPage whereReprLinkText($value)
 * @method static Builder|GalleryPage whereReprSubTitle($value)
 * @method static Builder|GalleryPage whereReprText($value)
 * @method static Builder|GalleryPage whereReprTitle($value)
 * @method static Builder|GalleryPage whereRightT1($value)
 * @method static Builder|GalleryPage whereRightT2($value)
 * @method static Builder|GalleryPage whereRightT3($value)
 * @method static Builder|GalleryPage whereRightTitle($value)
 * @method static Builder|GalleryPage whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GalleryPage whereUpdatedAt($value)
 * @method static Builder|GalleryPage withTranslation($locale = null, $fallback = true)
 * @method static Builder|GalleryPage withTranslations($locales = null, $fallback = true)
 */
class GalleryPage extends Model
{
    use Translatable;

    protected $table = 'gallery_page';

    protected $fillable = [];

    protected $translatable = [
        'meta_title', 'left_title', 'left_sub_title', 'right_title', 'right_t1', 'right_t2', 'right_t3', 'modc_title',
        'modc_sub', 'modc_text', 'modc_link_text', 'fotoc_title', 'meta_description', 'gal__desc',

        'fotoc_sub', 'fotoc_text', 'fotoc_link_text', 'repr_title', 'repr_sub_title', 'repr_text',

        'repr_link_text', '',
    ];
}
