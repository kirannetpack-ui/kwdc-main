<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'user_id',
        'transactionable_type',
        'transactionable_id',
        'amount',
        'tax',
        'payment_method',
        'transaction_id',
        'receipt_no',
        'status',
        'payment_date',
        'payment_details',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'payment_details' => 'array',
        'payment_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactionable()
    {
        return $this->morphTo();
    }

    // Helper Methods
    public static function generateReceiptNo()
    {
        $year = date('Y');
        $month = date('m');
        $lastTransaction = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction && $lastTransaction->receipt_no) {
            $lastNumber = intval(substr($lastTransaction->receipt_no, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "RCP-{$year}{$month}-{$newNumber}";
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Status Checks
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    // Actions
    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->payment_date = now();
        if (empty($this->receipt_no)) {
            $this->receipt_no = self::generateReceiptNo();
        }
        $this->save();
        return $this;
    }

    public function markAsFailed($reason = null)
    {
        $this->status = 'failed';
        if ($reason) {
            $this->notes = $reason;
        }
        $this->save();
        return $this;
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return 'रू ' . number_format($this->amount, 2);
    }

    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'completed':
                return 'bg-green-100 text-green-600';
            case 'pending':
                return 'bg-yellow-100 text-yellow-600';
            case 'failed':
                return 'bg-red-100 text-red-600';
            default:
                return 'bg-gray-100 text-gray-600';
        }
    }

    public function getStatusIconAttribute()
    {
        switch ($this->status) {
            case 'completed':
                return 'fa-check-circle';
            case 'pending':
                return 'fa-clock';
            case 'failed':
                return 'fa-times-circle';
            default:
                return 'fa-circle';
        }
    }
}