<?php

namespace App\Models;

use App\Mail\SendUserRevWasAdded;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mail;

/**
 * App\Models\OurWork
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $img
 * @property string|null $avatar
 * @property string|null $name
 * @property string|null $text
 * @property int|null $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $a_player
 * @property string|null $orig_locale
 * @property string|null $email
 * @property string|null $city
 * @method static Builder|OurWork newModelQuery()
 * @method static Builder|OurWork newQuery()
 * @method static Builder|OurWork query()
 * @method static Builder|OurWork whereAPlayer($value)
 * @method static Builder|OurWork whereActive($value)
 * @method static Builder|OurWork whereAvatar($value)
 * @method static Builder|OurWork whereCity($value)
 * @method static Builder|OurWork whereCreatedAt($value)
 * @method static Builder|OurWork whereEmail($value)
 * @method static Builder|OurWork whereId($value)
 * @method static Builder|OurWork whereImg($value)
 * @method static Builder|OurWork whereName($value)
 * @method static Builder|OurWork whereOrigLocale($value)
 * @method static Builder|OurWork whereText($value)
 * @method static Builder|OurWork whereUpdatedAt($value)
 */
class OurWork extends Model
{
    protected $table = 'our_works';

    protected $fillable = ['name', 'text', 'a_player', 'img', 'avatar', 'active', 'orig_locale', 'email', 'city'];

    public static function boot()
    {
        parent::boot();

        self::updating(function ($model) {
            if ($model->isDirty('active')) {
                if ($model->active == 1) {
                    if ($model->email != '') {
                        Mail::to($model->email)->send(new SendUserRevWasAdded($model));
                    }
                }
            }
        });
    }
}
