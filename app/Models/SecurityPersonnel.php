<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityPersonnel extends Model
{
    protected $fillable = [
        'agency_id', 'name', 'photo', 'employee_id', 'phone', 'email',
        'citizenship_number', 'dob', 'address', 'position', 'qualifications',
        'training_certificate', 'training_expiry', 'status', 'has_vehicle',
        'vehicle_type', 'shift_availability'
    ];

    protected $casts = [
        'dob' => 'date',
        'training_expiry' => 'date',
        'shift_availability' => 'array',
        'has_vehicle' => 'boolean',
    ];

protected $table = 'security_personnels';

    public function agency(): BelongsTo
    {
    return $this->belongsTo(SecurityAgency::class, 'agency_id');
    }
}