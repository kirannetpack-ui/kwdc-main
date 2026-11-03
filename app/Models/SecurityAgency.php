<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAgency extends Model
{
    protected $fillable = [
        'user_id', 'agency_name', 'registration_number', 'license_number',
        'address', 'phone', 'emergency_phone', 'email', 'services_offered',
        'year_established', 'pan_vat_number', 'certifications', 'logo',
        'registration_certificate_path', 'license_certificate_path', 'pan_vat_certificate_path',
        'status', 'approved_at', 'is_verified', 'admin_notes'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function personnel(): HasMany
    {
    return $this->hasMany(SecurityPersonnel::class, 'agency_id');
    }

    public function goods(): HasMany
    {
    return $this->hasMany(SecurityGood::class, 'agency_id');
    }

    public function rates(): HasMany
    {
        return $this->hasMany(AgencyRate::class, 'agency_id');
    }

    public function assignments(): HasMany
    {
    return $this->hasMany(SecurityAssignment::class, 'agency_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

public function incidents()
    {
        return $this->hasManyThrough(
            SecurityIncident::class,
            SecurityAssignment::class,
            'agency_id',
            'assignment_id',
            'id',
            'id'
        );
    }
}