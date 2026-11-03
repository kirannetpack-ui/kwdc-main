<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('equipment_jobs')) {
            Schema::create('equipment_jobs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('equipment_id')->nullable()->constrained();
                $table->foreignId('owner_id')->nullable()->constrained('users');
                $table->foreignId('client_id')->nullable()->constrained('users');
                $table->foreignId('equipment_owner_id')->nullable()->constrained('users');
                
                // Job details
                $table->string('job_type')->nullable();
                $table->text('pickup_location')->nullable();
                $table->text('delivery_location')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->text('description')->nullable();
                $table->string('location')->nullable();
                
                // Pricing
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('proposed_price', 10, 2)->nullable();
                $table->decimal('amount', 10, 2)->default(0);
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->decimal('client_counter_price', 10, 2)->nullable();
                
                // Messages
                $table->text('proposal_message')->nullable();
                $table->text('client_message')->nullable();
                
                // Status
                $table->string('status')->default('pending');
                $table->string('accepted_by_client_status')->nullable();
                $table->string('accepted_by_owner_status')->nullable();
                
                // Timestamps
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('proposed_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('request_date')->nullable();
                $table->timestamp('completion_date')->nullable();
                
                $table->timestamps();
                
                $table->index('owner_id');
                $table->index('client_id');
                $table->index('status');
                $table->index('equipment_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('equipment_jobs');
    }
};