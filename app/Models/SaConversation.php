<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaConversation extends Model
{
    protected $table = 'sa_conversations';

    protected $guarded = [];

    protected $casts = [
        'last_message_at' => 'datetime',
        'unread_for_manager' => 'boolean',
    ];
}
