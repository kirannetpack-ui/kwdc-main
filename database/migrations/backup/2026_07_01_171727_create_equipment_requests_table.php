<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_requests', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            
            // Request details
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('equipment_type')->nullable();
            $table->string('equipment_name')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            
            // Dates
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('duration_days')->nullable();
            
            // Pricing
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('proposed_price', 10, 2)->nullable();
            $table->decimal('agreed_price', 10, 2)->nullable();
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'assigned', 'completed', 'cancelled'])
                ->default('pending');
            
            // Timestamps
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('set null');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['client_id', 'status']);
            $table->index(['equipment_id', 'status']);
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_requests');
    }
};