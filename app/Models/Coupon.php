<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Coupon
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $value
 * @property int|null $is_30_40_free
 * @property int|null $is_dates_sale
 * @property int|null $is_universal
 * @method static Builder|Coupon newModelQuery()
 * @method static Builder|Coupon newQuery()
 * @method static Builder|Coupon query()
 * @method static Builder|Coupon whereCreatedAt($value)
 * @method static Builder|Coupon whereId($value)
 * @method static Builder|Coupon whereIs3040Free($value)
 * @method static Builder|Coupon whereIsDatesSale($value)
 * @method static Builder|Coupon whereIsUniversal($value)
 * @method static Builder|Coupon whereText($value)
 * @method static Builder|Coupon whereUpdatedAt($value)
 * @method static Builder|Coupon whereValue($value)
 */
class Coupon extends Model
{
    public static function boot()
    {
        parent::boot();

        self::created(function ($model) {
            $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';

            $coupon_code = substr(str_shuffle($rand_code), 0, 10);

            Coupon::where('id', $model->id)->update(
                [
                    'text' => $coupon_code,
                ]
            );
        });
    }
}
