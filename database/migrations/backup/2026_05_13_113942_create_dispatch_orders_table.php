<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dispatch_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_request_id')->constrained()->onDelete('cascade');
            $table->string('destination_address');
            $table->string('pan_vat_bill')->nullable(); // file path
            $table->enum('status', ['pending', 'assigned', 'delivered'])->default('pending');
            $table->foreignId('assigned_vehicle_id')->nullable()->constrained('vehicles');
            $table->text('proof_of_delivery_photo')->nullable(); // file path
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dispatch_orders');
    }
};