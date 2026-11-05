<?php

namespace App\Models;

use Eloquent;
use App\Models\OrderUserComments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\OrderPainterImages
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property int|null $orders_id
 * @property string|null $comment
 * @property int|null $is_admin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|OrderPainterImages newModelQuery()
 * @method static Builder|OrderPainterImages newQuery()
 * @method static Builder|OrderPainterImages query()
 * @method static Builder|OrderPainterImages whereComment($value)
 * @method static Builder|OrderPainterImages whereCreatedAt($value)
 * @method static Builder|OrderPainterImages whereId($value)
 * @method static Builder|OrderPainterImages whereIsAdmin($value)
 * @method static Builder|OrderPainterImages whereOrdersId($value)
 * @method static Builder|OrderPainterImages whereUpdatedAt($value)
 */
class OrderPainterImages extends Model
{
    public $timestamps = true;
    protected $table = 'order_painter_images';


    public function getLastOrderByCurrentUser()
    {
        $orders = DB::table('orders')->where('user_id', Auth::id())->orderBy('created_at', 'desc')->first();

        return $orders;
    }

    public function order_user_comments()
    {
        return $this->hasMany(OrderUserComments::class, "order_painter_image_id");
    }

    public function statusDefinition()
    {
        return $this->belongsTo(APainterImagesStatus::class, 'status');
    }

    
}
