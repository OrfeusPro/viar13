<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class VenipakData extends Model
{
    protected $table = 'venipak_data';

    protected $fillable = [
        'user',
        'pass',
        'login_id',
        'import_url',
        'print_url',
        's_name',
        's_code',
        's_country',
        's_city',
        's_address',
        's_post',
        's_contact_p',
        's_contact_t',
        'email_sender',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope для вибірки активного запису
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Boot method для автоматичної деактивації інших записів
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Якщо поточний запис активується, деактивуємо всі інші
            if ($model->is_active) {
                static::where('id', '!=', $model->id)->update(['is_active' => false]);
            }
        });
    }
}
