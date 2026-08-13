<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderAction
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $user
 * @property string|null $activity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|OrderAction newModelQuery()
 * @method static Builder|OrderAction newQuery()
 * @method static Builder|OrderAction query()
 * @method static Builder|OrderAction whereActivity($value)
 * @method static Builder|OrderAction whereCreatedAt($value)
 * @method static Builder|OrderAction whereId($value)
 * @method static Builder|OrderAction whereUpdatedAt($value)
 * @method static Builder|OrderAction whereUser($value)
 */
class OrderAction extends Model
{
    protected $table = 'order_action';

    protected $fillable = ['user', 'activity'];
}
