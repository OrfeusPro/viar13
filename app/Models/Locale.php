<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Locale
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string $prefix
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Locale newModelQuery()
 * @method static Builder|Locale newQuery()
 * @method static Builder|Locale query()
 * @method static Builder|Locale whereCreatedAt($value)
 * @method static Builder|Locale whereId($value)
 * @method static Builder|Locale whereName($value)
 * @method static Builder|Locale wherePrefix($value)
 * @method static Builder|Locale whereUpdatedAt($value)
 */
class Locale extends Model
{
    protected $fillable = [

        'prefix', 'name',

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function getlocales()
    {
        return DB::table('locales')->get();
    }
}
