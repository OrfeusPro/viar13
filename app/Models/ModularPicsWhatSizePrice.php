<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\ModularPicsWhatSizePrice
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $what_title
 * @property string|null $what_text1
 * @property string|null $what_text2
 * @property string|null $what_text3
 * @property string|null $diff_size_title
 * @property string|null $diff_size_text_top
 * @property string|null $diff_size_text_center
 * @property string|null $diff_size_bot_title
 * @property string|null $diff_size_bot_list
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|ModularPicsWhatSizePrice newModelQuery()
 * @method static Builder|ModularPicsWhatSizePrice newQuery()
 * @method static Builder|ModularPicsWhatSizePrice query()
 * @method static Builder|ModularPicsWhatSizePrice whereCreatedAt($value)
 * @method static Builder|ModularPicsWhatSizePrice whereDiffSizeBotList($value)
 * @method static Builder|ModularPicsWhatSizePrice whereDiffSizeBotTitle($value)
 * @method static Builder|ModularPicsWhatSizePrice whereDiffSizeTextCenter($value)
 * @method static Builder|ModularPicsWhatSizePrice whereDiffSizeTextTop($value)
 * @method static Builder|ModularPicsWhatSizePrice whereDiffSizeTitle($value)
 * @method static Builder|ModularPicsWhatSizePrice whereId($value)
 * @method static Builder|ModularPicsWhatSizePrice whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|ModularPicsWhatSizePrice whereUpdatedAt($value)
 * @method static Builder|ModularPicsWhatSizePrice whereWhatText1($value)
 * @method static Builder|ModularPicsWhatSizePrice whereWhatText2($value)
 * @method static Builder|ModularPicsWhatSizePrice whereWhatText3($value)
 * @method static Builder|ModularPicsWhatSizePrice whereWhatTitle($value)
 * @method static Builder|ModularPicsWhatSizePrice withTranslation($locale = null, $fallback = true)
 * @method static Builder|ModularPicsWhatSizePrice withTranslations($locales = null, $fallback = true)
 */
class ModularPicsWhatSizePrice extends Model
{
    use Translatable;

    protected $table = 'modular_pics_what_size_price';

    protected $fillable = [];

    protected $translatable = [
        'what_title', 'what_text1', 'what_text2', 'what_text3', 'diff_size_title', 'diff_size_text_top',

        'diff_size_text_center', 'diff_size_bot_title', 'diff_size_bot_list',
    ];
}
