<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'stocks';

    protected $fillable = [
        'product_name',
        'user_id',
        'description',
        'unit',
        'number_of_boxes',
        'quantity_per_box',
        'total_quantity',
        'batch_id',
        'sku',
        'invoice_number',
        'invoice_file_path',
        'warehouse_id',
        'warehouse_name',
        'client_code',
        'client_name',
        'grn_file_path',
        'quality_certificate_path',
        'other_documents_path',
        'manufacturing_date',
        'expiry_date',
        'received_date',
        'qr_code_path',
        'qr_code_data',
        'status',
        'remaining_quantity',
        'purchase_price',
        'selling_price',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'received_date' => 'date',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    // Generate Batch ID: BID-CLT-2024-0001 format
    public static function generateBatchId($clientCode)
    {
        $year = now()->year;
        $clientPrefix = substr($clientCode, 0, 3); // CLT, DRV, EQO
        
        $lastBatch = self::where('batch_id', 'like', "BID-{$clientPrefix}-{$year}-%")
            ->orderBy('batch_id', 'desc')
            ->first();

        if ($lastBatch) {
            $lastNumber = intval(substr($lastBatch->batch_id, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "BID-{$clientPrefix}-{$year}-{$newNumber}";
    }

    // Generate SKU
    public static function generateSKU()
    {
        $year = now()->year;
        $lastSKU = self::where('sku', 'like', "PROD-{$year}-%")
            ->orderBy('sku', 'desc')
            ->first();

        if ($lastSKU) {
            $lastNumber = intval(substr($lastSKU->sku, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "PROD-{$year}-{$newNumber}";
    }

    // Relationships
    public function client()
{
    return $this->belongsTo(User::class, 'client_id');
}


    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

public function warehouseRequest()
    {
        return $this->belongsTo(WarehouseRequest::class, 'warehouse_request_id');
    }
}
