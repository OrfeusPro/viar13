<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaMessage extends Model
{
    protected $table = 'sa_messages';

    protected $guarded = [];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}

