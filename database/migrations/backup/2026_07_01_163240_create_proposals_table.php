<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('warehouse_request_id')->nullable();
            
            // Proposal details
            $table->decimal('proposed_price', 10, 2);
            $table->decimal('negotiated_price', 10, 2)->nullable();
            $table->text('negotiation_message')->nullable();
            $table->text('description')->nullable();
            
            // Validity
            $table->timestamp('valid_until')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'accepted', 'rejected', 'negotiating', 'expired'])
                ->default('pending');
            
            // Timestamps
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('warehouse_request_id')->references('id')->on('warehouse_requests')->onDelete('set null');
            
            // Indexes
            $table->index(['client_id', 'status']);
            $table->index(['warehouse_id', 'status']);
            $table->index('status');
            $table->index('valid_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};