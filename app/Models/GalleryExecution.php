<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\GalleryExecution
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string $name
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|GalleryExecution newModelQuery()
 * @method static Builder|GalleryExecution newQuery()
 * @method static Builder|GalleryExecution query()
 * @method static Builder|GalleryExecution whereCreatedAt($value)
 * @method static Builder|GalleryExecution whereId($value)
 * @method static Builder|GalleryExecution whereName($value)
 * @method static Builder|GalleryExecution wherePrice($value)
 * @method static Builder|GalleryExecution whereUpdatedAt($value)
 */
class GalleryExecution extends Model
{
    //
}
