<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('boxes', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->string('qr_code')->unique();
            $table->string('barcode')->unique();
            $table->date('entry_date');
            $table->string('invoice_number');
            $table->string('shipper_name');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->integer('total_boxes');
            $table->integer('box_number');
            $table->string('status')->default('pending'); // pending, in_transit, delivered, received
            $table->foreignId('stock_id')->nullable()->constrained('stocks')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->string('received_by')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('boxes');
    }
};