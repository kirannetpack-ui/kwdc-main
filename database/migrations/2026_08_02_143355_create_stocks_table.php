<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('stocks')) {
            Schema::create('stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users');
                $table->foreignId('client_id')->nullable()->constrained('users');
                $table->foreignId('warehouse_request_id')->nullable()->constrained();
                $table->foreignId('warehouse_id')->nullable()->constrained();
                
                // Product details
                $table->string('product_name');
                $table->text('description')->nullable();
                $table->string('unit')->default('pieces');
                $table->integer('number_of_boxes')->default(0);
                $table->integer('quantity_per_box')->default(0);
                $table->integer('total_quantity')->default(0);
                $table->integer('remaining_quantity')->nullable();
                
                // Tracking
                $table->string('batch_id')->unique();
                $table->string('sku')->unique();
                $table->string('invoice_number')->nullable();
                $table->string('invoice_file_path')->nullable();
                $table->string('warehouse_name')->nullable();
                
                // Client info
                $table->string('client_code');
                $table->string('client_name');
                
                // Documents
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
                $table->string('qr_code')->nullable();
                
                // Pricing
                $table->decimal('purchase_price', 10, 2)->nullable();
                $table->decimal('selling_price', 10, 2)->nullable();
                
                // Status
                $table->enum('status', ['in_stock', 'partial', 'dispatched', 'expired'])->default('in_stock');
                
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('batch_id');
                $table->index('sku');
                $table->index('status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('stocks');
    }
};