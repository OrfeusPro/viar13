<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PortBeforeAfter
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $img_before
 * @property string|null $img_after
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $main_image
 * @method static Builder|PortBeforeAfter newModelQuery()
 * @method static Builder|PortBeforeAfter newQuery()
 * @method static Builder|PortBeforeAfter query()
 * @method static Builder|PortBeforeAfter whereCreatedAt($value)
 * @method static Builder|PortBeforeAfter whereId($value)
 * @method static Builder|PortBeforeAfter whereImgAfter($value)
 * @method static Builder|PortBeforeAfter whereImgBefore($value)
 * @method static Builder|PortBeforeAfter whereMainImage($value)
 * @method static Builder|PortBeforeAfter whereUpdatedAt($value)
 */
class PortBeforeAfter extends Model
{
    protected $table = 'port_before_after';
    protected $fillable = [];
}
