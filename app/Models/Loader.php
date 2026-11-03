<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loader extends Model
{
    protected $fillable = [
        'name', 'phone', 'address', 'citizenship_number', 'dob', 'photo',
        'emergency_contact', 'is_available', 'rate_per_hour', 'skills',
        'experience_years', 'status'
    ];

    protected $casts = [
        'dob' => 'date',
        'is_available' => 'boolean',
        'rate_per_hour' => 'decimal:2',
        'skills' => 'array',
    ];

    public function jobLogs(): HasMany
    {
        return $this->hasMany(LoaderJobLog::class);
    }
}