<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\VrNumber
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property int $order_id
 * @property int|null $vrv_1
 * @property int|null $vrv_2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|VrNumber newModelQuery()
 * @method static Builder|VrNumber newQuery()
 * @method static Builder|VrNumber query()
 * @method static Builder|VrNumber whereCreatedAt($value)
 * @method static Builder|VrNumber whereId($value)
 * @method static Builder|VrNumber whereOrderId($value)
 * @method static Builder|VrNumber whereUpdatedAt($value)
 * @method static Builder|VrNumber whereVrv1($value)
 * @method static Builder|VrNumber whereVrv2($value)
 */
class VrNumber extends Model
{
}
