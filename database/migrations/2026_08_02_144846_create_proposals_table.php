<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('proposals')) {
            Schema::create('proposals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('users');
                $table->foreignId('driver_id')->nullable()->constrained('users');
                $table->foreignId('warehouse_request_id')->nullable()->constrained();
                $table->foreignId('warehouse_id')->nullable()->constrained();
                
                // Job details
                $table->string('job_type')->nullable();
                $table->unsignedBigInteger('job_id')->nullable();
                $table->string('job_reference')->nullable();
                
                // Pricing
                $table->decimal('proposed_price', 10, 2);
                $table->decimal('counter_price', 10, 2)->nullable();
                $table->decimal('client_counter_price', 10, 2)->nullable();
                $table->decimal('negotiated_price', 10, 2)->nullable();
                $table->decimal('final_price', 10, 2)->nullable();
                
                // Messages
                $table->text('message')->nullable();
                $table->text('client_message')->nullable();
                $table->text('negotiation_message')->nullable();
                $table->text('description')->nullable();
                $table->text('notes')->nullable();
                
                // Dates
                $table->timestamp('valid_until')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->timestamp('driver_response_at')->nullable();
                $table->timestamp('client_response_at')->nullable();
                
                // Status
                $table->string('status')->default('pending');
                
                $table->timestamps();
                
                $table->index(['client_id', 'status']);
                $table->index(['driver_id', 'status']);
                $table->index('status');
                $table->index('job_type');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('proposals');
    }
};