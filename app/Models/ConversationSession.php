<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationSession extends Model
{
    protected $fillable = ['user_id', 'intent', 'context', 'collected_data', 'status', 'expires_at'];

    protected $casts = [
        'context' => 'array',
        'collected_data' => 'array',
    ];
}