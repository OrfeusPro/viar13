<?php

namespace TCG\Voyager\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function getLocaleAttribute(): ?string
    {
        // Voyager stores this virtual attribute in settings, not a users.locale column.
        $locale = data_get($this->settings, 'locale');

        return is_string($locale) ? $locale : null;
    }
}
