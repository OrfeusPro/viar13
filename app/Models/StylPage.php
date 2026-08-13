<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\StylPage
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $meta_desc
 * @property string|null $title
 * @property string|null $subtitle
 * @property string|null $style_title
 * @property string|null $more_link_text
 * @property string|null $gift_title
 * @property string|null $gift_sub
 * @property string|null $gift_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $style_sub_text
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|StylPage newModelQuery()
 * @method static Builder|StylPage newQuery()
 * @method static Builder|StylPage query()
 * @method static Builder|StylPage whereCreatedAt($value)
 * @method static Builder|StylPage whereGiftOrder($value)
 * @method static Builder|StylPage whereGiftSub($value)
 * @method static Builder|StylPage whereGiftTitle($value)
 * @method static Builder|StylPage whereId($value)
 * @method static Builder|StylPage whereMetaDesc($value)
 * @method static Builder|StylPage whereMetaTitle($value)
 * @method static Builder|StylPage whereMoreLinkText($value)
 * @method static Builder|StylPage whereStyleSubText($value)
 * @method static Builder|StylPage whereStyleTitle($value)
 * @method static Builder|StylPage whereSubtitle($value)
 * @method static Builder|StylPage whereTitle($value)
 * @method static Builder|StylPage whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|StylPage whereUpdatedAt($value)
 * @method static Builder|StylPage withTranslation($locale = null, $fallback = true)
 * @method static Builder|StylPage withTranslations($locales = null, $fallback = true)
 */
class StylPage extends Model
{
    use Translatable;

    protected $table = 'styl_page';

    protected $fillable = [];

    protected $translatable = [
        'meta_title', 'meta_desc', 'style_sub_text',

        'title', 'subtitle', 'style_title', 'more_link_text', 'gift_title', 'gift_sub', 'gift_order',
    ];
}
