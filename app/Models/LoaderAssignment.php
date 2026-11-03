<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoaderAssignment extends Model
{
    protected $fillable = [
        'dispatch_id', 'loader_manager_id', 'loader_ids', 'required_loaders',
        'assignment_date', 'start_time', 'end_time', 'total_cost', 'status', 'notes'
    ];

    protected $casts = [
        'loader_ids' => 'array',
        'assignment_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_cost' => 'decimal:2',
    ];

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(DispatchOrder::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(LoaderManager::class, 'loader_manager_id');
    }
}