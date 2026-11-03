<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warehouse_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->decimal('required_space', 10, 2); // cubic meters
            $table->integer('duration_months');
            $table->string('invoice_path')->nullable();
            $table->string('packing_list_path')->nullable();
            $table->string('insurance_path')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->foreignId('assigned_warehouse_id')->nullable()->constrained('warehouses');
            $table->enum('status', ['pending', 'assigned', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warehouse_requests');
    }
};