<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgencyRate extends Model
{
    protected $fillable = [
        'agency_id', 'service_type', 'rate_type', 'rate', 'description', 'is_active'
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(SecurityAgency::class, 'agency_id');
    }
}