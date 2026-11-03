<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'notes',
        'starts_at',
        'remind_at',
        'emailed_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'remind_at' => 'datetime',
        'emailed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDueForEmail($query)
    {
        return $query->where('status', 'scheduled')
            ->whereNull('emailed_at')
            ->whereNotNull('remind_at')
            ->where('remind_at', '<=', now());
    }
}
