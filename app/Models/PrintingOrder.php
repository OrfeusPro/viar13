<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PrintingOrder
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property int|null $order_id
 * @property int|null $user_id
 * @property int|null $in_work
 * @property int|null $is_payed
 * @property string|null $picked_at
 * @property string|null $complete_until
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $is_completed
 * @method static Builder|PrintingOrder newModelQuery()
 * @method static Builder|PrintingOrder newQuery()
 * @method static Builder|PrintingOrder query()
 * @method static Builder|PrintingOrder whereCompleteUntil($value)
 * @method static Builder|PrintingOrder whereCreatedAt($value)
 * @method static Builder|PrintingOrder whereId($value)
 * @method static Builder|PrintingOrder whereInWork($value)
 * @method static Builder|PrintingOrder whereIsCompleted($value)
 * @method static Builder|PrintingOrder whereIsPayed($value)
 * @method static Builder|PrintingOrder whereOrderId($value)
 * @method static Builder|PrintingOrder wherePickedAt($value)
 * @method static Builder|PrintingOrder whereUpdatedAt($value)
 * @method static Builder|PrintingOrder whereUserId($value)
 */
class PrintingOrder extends Model
{
    protected $table = 'printing_orders';

    protected $fillable = ['order_id', 'user_id'];
}
