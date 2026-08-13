<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CanvasInterier
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $inter_img
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $size_1
 * @property int|null $size_2
 * @method static Builder|CanvasInterier newModelQuery()
 * @method static Builder|CanvasInterier newQuery()
 * @method static Builder|CanvasInterier query()
 * @method static Builder|CanvasInterier whereCreatedAt($value)
 * @method static Builder|CanvasInterier whereId($value)
 * @method static Builder|CanvasInterier whereInterImg($value)
 * @method static Builder|CanvasInterier whereSize1($value)
 * @method static Builder|CanvasInterier whereSize2($value)
 * @method static Builder|CanvasInterier whereUpdatedAt($value)
 */
class CanvasInterier extends Model
{
    protected $fillable = [];
}
