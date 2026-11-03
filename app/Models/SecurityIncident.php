<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityIncident extends Model
{
    protected $fillable = [
        'warehouse_id', 'reported_by_user_id', 'assignment_id', 'incident_time',
        'category', 'description', 'severity', 'actions_taken', 'attachments', 'status'
    ];

    protected $casts = [
        'incident_time' => 'datetime',
        'attachments' => 'array',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(SecurityAssignment::class);
    }
}