<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAssignment extends Model
{
    protected $fillable = [
        'warehouse_id', 'agency_id', 'personnel_id', 'start_date', 'end_date',
        'shift', 'shift_start', 'shift_end', 'status', 'notes', 'total_cost'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_cost' => 'decimal:2',
        'shift_start' => 'datetime',
        'shift_end' => 'datetime',
    ];

protected $table = 'security_assignments';

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function agency(): BelongsTo
    {
    return $this->belongsTo(SecurityAgency::class, 'agency_id');
    }

    public function personnel(): BelongsTo
    {
    return $this->belongsTo(SecurityPersonnel::class, 'personnel_id');
    }
}