<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\SendRev
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $meta_title
 * @property string|null $u_name
 * @property string|null $u_mail
 * @property string|null $u_ava
 * @property string|null $u_promo
 * @property string|null $u_audio
 * @property string|null $u_text
 * @property string|null $u_send
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $need_reg
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|SendRev newModelQuery()
 * @method static Builder|SendRev newQuery()
 * @method static Builder|SendRev query()
 * @method static Builder|SendRev whereCreatedAt($value)
 * @method static Builder|SendRev whereId($value)
 * @method static Builder|SendRev whereMetaTitle($value)
 * @method static Builder|SendRev whereNeedReg($value)
 * @method static Builder|SendRev whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|SendRev whereUAudio($value)
 * @method static Builder|SendRev whereUAva($value)
 * @method static Builder|SendRev whereUMail($value)
 * @method static Builder|SendRev whereUName($value)
 * @method static Builder|SendRev whereUPromo($value)
 * @method static Builder|SendRev whereUSend($value)
 * @method static Builder|SendRev whereUText($value)
 * @method static Builder|SendRev whereUpdatedAt($value)
 * @method static Builder|SendRev withTranslation($locale = null, $fallback = true)
 * @method static Builder|SendRev withTranslations($locales = null, $fallback = true)
 */
class SendRev extends Model
{
    use Translatable;

    protected $table = 'send_rev';
    protected $fillable = [];

    protected $translatable = [
        'meta_title', 'u_name', 'u_mail', 'u_ava', 'u_promo', 'u_audio', 'u_text', 'u_send', 'need_reg'
    ];
}
