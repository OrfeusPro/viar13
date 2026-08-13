<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\GalleryTagColor
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $name
 * @method static Builder|GalleryTagColor newModelQuery()
 * @method static Builder|GalleryTagColor newQuery()
 * @method static Builder|GalleryTagColor query()
 * @method static Builder|GalleryTagColor whereColor($value)
 * @method static Builder|GalleryTagColor whereCreatedAt($value)
 * @method static Builder|GalleryTagColor whereId($value)
 * @method static Builder|GalleryTagColor whereName($value)
 * @method static Builder|GalleryTagColor whereUpdatedAt($value)
 */
class GalleryTagColor extends Model
{
    protected $fillable = [

        'id', 'color', 'name',

    ];

    public function getTagForCategory()
    {
        return GalleryTagColor::get();
    }
}
