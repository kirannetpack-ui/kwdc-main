<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if table already exists before creating
        if (!Schema::hasTable('stocks')) {
            Schema::create('stocks', function (Blueprint $table) {
                $table->id();
                
                // Basic Product Information
                $table->string('product_name');
                $table->text('description')->nullable();
                $table->string('unit')->default('pieces');
                
                // Box/Quantity Information
                $table->integer('number_of_boxes');
                $table->integer('quantity_per_box');
                $table->integer('total_quantity');
                
                // Batch & Tracking Information
                $table->string('batch_id')->unique();
                $table->string('sku')->unique();
                
                // Invoice Information
                $table->string('invoice_number')->nullable();
                $table->string('invoice_file_path')->nullable();
                
                // Warehouse Assignment
                $table->unsignedBigInteger('warehouse_id')->nullable();
                $table->string('warehouse_name')->nullable();
                
                // Client Information
                $table->string('client_code');
                $table->string('client_name');
                
                // Document Uploads
                $table->string('grn_file_path')->nullable();
                $table->string('quality_certificate_path')->nullable();
                $table->string('other_documents_path')->nullable();
                
                // Dates
                $table->date('manufacturing_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->date('received_date');
                
                // QR Code
                $table->string('qr_code_path')->nullable();
                $table->text('qr_code_data')->nullable();
                
                // Status
                $table->enum('status', ['in_stock', 'partial', 'dispatched', 'expired'])->default('in_stock');
                $table->integer('remaining_quantity');
                
                // Pricing
                $table->decimal('purchase_price', 10, 2)->nullable();
                $table->decimal('selling_price', 10, 2)->nullable();
                
                $table->timestamps();
                
                // Indexes
                $table->index('batch_id');
                $table->index('sku');
                $table->index('client_code');
                $table->index('warehouse_id');
                $table->index('invoice_number');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};