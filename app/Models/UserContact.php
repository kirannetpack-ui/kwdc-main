<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserContact extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'whatsapp',
        'relation',
        'is_primary',
        'receive_emails',
        'receive_sms',
        'receive_whatsapp',
    ];
    
    protected $casts = [
        'is_primary' => 'boolean',
        'receive_emails' => 'boolean',
        'receive_sms' => 'boolean',
        'receive_whatsapp' => 'boolean',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}