<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaEvent extends Model
{
    protected $table = 'sa_events';

    protected $guarded = [];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}

