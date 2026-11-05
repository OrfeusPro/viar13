<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrdersChats
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property int|null $orders_id
 * @property string|null $comment
 * @property int|null $is_admin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|OrdersChats newModelQuery()
 * @method static Builder|OrdersChats newQuery()
 * @method static Builder|OrdersChats query()
 * @method static Builder|OrdersChats whereComment($value)
 * @method static Builder|OrdersChats whereCreatedAt($value)
 * @method static Builder|OrdersChats whereId($value)
 * @method static Builder|OrdersChats whereIsAdmin($value)
 * @method static Builder|OrdersChats whereOrdersId($value)
 * @method static Builder|OrdersChats whereUpdatedAt($value)
 */
class AdminChats extends Model
{
    public $timestamps = true;
    protected $table = 'order_admin_comments';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
