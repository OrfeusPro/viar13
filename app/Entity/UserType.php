<?php

namespace App\Entity;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Entity\UserType
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|UserType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserType query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserType whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static \Illuminate\Database\Eloquent\Builder|UserType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserType withTranslation($locale = null, $fallback = true)
 * @method static \Illuminate\Database\Eloquent\Builder|UserType withTranslations($locales = null, $fallback = true)
 * @mixin \Eloquent
 */
class UserType extends Model
{
    use Translatable;

    protected $table = 'user_types';

    protected $translatable = ['name'];

    protected $fillable = [

        'name',

    ];

    public function users()
    {
        return $this->hasMany(User::class, 'type_id', 'id');
    }
}
