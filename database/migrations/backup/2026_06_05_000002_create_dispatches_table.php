<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('client_code');
            $table->foreignId('driver_id')->nullable()->constrained('users');
            $table->string('driver_code')->nullable();
            $table->foreignId('vehicle_id')->nullable();
            
            $table->string('pickup_location');
            $table->string('delivery_location');
            $table->decimal('distance_km', 10, 2)->nullable();
            
            $table->string('item_description');
            $table->integer('quantity')->default(1);
            $table->decimal('weight_kg', 10, 2)->nullable();
            
            $table->string('status')->default('pending');
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('driver_earning', 10, 2)->nullable();
            $table->decimal('admin_commission', 10, 2)->nullable();
            
            $table->decimal('proposed_price', 10, 2)->nullable();
            $table->string('proposal_status')->nullable();
            
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('delivery_proof')->nullable();
            
            $table->string('tracking_code')->nullable();
            $table->text('special_instructions')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('request_number');
            $table->index('client_id');
            $table->index('driver_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatches');
    }
};