<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderUserImages
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property int|null $orders_id
 * @property string|null $comment
 * @property int|null $is_admin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|OrderUserImages newModelQuery()
 * @method static Builder|OrderUserImages newQuery()
 * @method static Builder|OrderUserImages query()
 * @method static Builder|OrderUserImages whereComment($value)
 * @method static Builder|OrderUserImages whereCreatedAt($value)
 * @method static Builder|OrderUserImages whereId($value)
 * @method static Builder|OrderUserImages whereIsAdmin($value)
 * @method static Builder|OrderUserImages whereOrdersId($value)
 * @method static Builder|OrderUserImages whereUpdatedAt($value)
 */
class OrderUserImages extends Model
{
    public $timestamps = true;
    protected $table = 'order_user_images';
}
