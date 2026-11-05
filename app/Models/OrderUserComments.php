<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderUserComments
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property int|null $orders_id
 * @property string|null $comment
 * @property int|null $is_admin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|OrderUserComments newModelQuery()
 * @method static Builder|OrderUserComments newQuery()
 * @method static Builder|OrderUserComments query()
 * @method static Builder|OrderUserComments whereComment($value)
 * @method static Builder|OrderUserComments whereCreatedAt($value)
 * @method static Builder|OrderUserComments whereId($value)
 * @method static Builder|OrderUserComments whereIsAdmin($value)
 * @method static Builder|OrderUserComments whereOrdersId($value)
 * @method static Builder|OrderUserComments whereUpdatedAt($value)
 */
class OrderUserComments extends Model
{
    public $timestamps = true;
    protected $table = 'order_user_comments';

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
