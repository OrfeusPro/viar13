<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\CountryTel
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $phone_code
 * @property string|null $country_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $country_code
 * @property int|null $deliv_price
 * @property int|null $high_price
 * @property string|null $price_country_mltpr
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CountryTel newModelQuery()
 * @method static Builder|CountryTel newQuery()
 * @method static Builder|CountryTel query()
 * @method static Builder|CountryTel whereCountryCode($value)
 * @method static Builder|CountryTel whereCountryName($value)
 * @method static Builder|CountryTel whereCreatedAt($value)
 * @method static Builder|CountryTel whereDelivPrice($value)
 * @method static Builder|CountryTel whereHighPrice($value)
 * @method static Builder|CountryTel whereId($value)
 * @method static Builder|CountryTel wherePhoneCode($value)
 * @method static Builder|CountryTel wherePriceCountryMltpr($value)
 * @method static Builder|CountryTel whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CountryTel whereUpdatedAt($value)
 * @method static Builder|CountryTel withTranslation($locale = null, $fallback = true)
 * @method static Builder|CountryTel withTranslations($locales = null, $fallback = true)
 */
class CountryTel extends Model
{
    use Translatable;

    protected $table = 'country_tels';

    protected $translatable = ['country_name'];
}
