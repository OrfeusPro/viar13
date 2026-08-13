<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\GallerySize
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string $size
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|GallerySize newModelQuery()
 * @method static Builder|GallerySize newQuery()
 * @method static Builder|GallerySize query()
 * @method static Builder|GallerySize whereCreatedAt($value)
 * @method static Builder|GallerySize whereId($value)
 * @method static Builder|GallerySize wherePrice($value)
 * @method static Builder|GallerySize whereSize($value)
 * @method static Builder|GallerySize whereUpdatedAt($value)
 */
class GallerySize extends Model
{
    protected $fillable = [
        'id',
        'size',
        'price',
        'height',
        'length',
    ];

    public function getHeight()
    {
        return isset(explode('x', $this->size)[0]) ? explode('x', $this->size)[0] : '';
    }

    public function getLength()
    {
        return isset(explode('x', $this->size)[1]) ? explode('x', $this->size)[1] : '';
    }
}
