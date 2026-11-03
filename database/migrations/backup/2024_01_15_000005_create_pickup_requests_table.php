<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if table already exists before creating
        if (!Schema::hasTable('pickup_requests')) {
            Schema::create('pickup_requests', function (Blueprint $table) {
                $table->id();
                $table->string('request_number')->unique();
                $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
                $table->string('client_code');
                $table->foreignId('driver_id')->nullable()->constrained('users');
                $table->string('driver_code')->nullable();
                $table->string('pickup_location');
                $table->string('delivery_location');
                $table->text('description')->nullable();
                $table->integer('quantity');
                $table->string('unit')->default('pieces');
                $table->decimal('price', 10, 2)->nullable();
                $table->decimal('agreed_price', 10, 2)->nullable();
                $table->decimal('total_amount', 10, 2)->nullable();
                $table->enum('status', ['pending', 'assigned', 'picked_up', 'delivered', 'cancelled'])->default('pending');
                $table->date('pickup_date')->nullable();
                $table->date('delivery_date')->nullable();
                $table->timestamps();
                
                $table->index('request_number');
                $table->index('client_code');
                $table->index('driver_code');
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_requests');
    }
};