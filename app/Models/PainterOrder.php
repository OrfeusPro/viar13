<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PainterOrder
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
 * @method static Builder|PainterOrder newModelQuery()
 * @method static Builder|PainterOrder newQuery()
 * @method static Builder|PainterOrder query()
 * @method static Builder|PainterOrder whereCompleteUntil($value)
 * @method static Builder|PainterOrder whereCreatedAt($value)
 * @method static Builder|PainterOrder whereId($value)
 * @method static Builder|PainterOrder whereInWork($value)
 * @method static Builder|PainterOrder whereIsCompleted($value)
 * @method static Builder|PainterOrder whereIsPayed($value)
 * @method static Builder|PainterOrder whereOrderId($value)
 * @method static Builder|PainterOrder wherePickedAt($value)
 * @method static Builder|PainterOrder whereUpdatedAt($value)
 * @method static Builder|PainterOrder whereUserId($value)
 */
class PainterOrder extends Model
{
    protected $table = 'painter_orders';

    protected $fillable = ['order_id', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
