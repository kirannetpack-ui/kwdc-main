<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pickup_requests')) {
            Schema::create('pickup_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('users');
                $table->foreignId('driver_id')->nullable()->constrained('users');
                $table->foreignId('warehouse_id')->nullable()->constrained();
                
                // Tracking
                $table->string('tracking_id')->unique()->nullable();
                $table->string('invoice_no')->unique()->nullable();
                
                // Details
                $table->decimal('total_distance', 10, 2)->default(0);
                $table->decimal('total_price', 10, 2)->default(0);
                $table->decimal('tax_amount', 10, 2)->default(0);
                $table->decimal('grand_total', 10, 2)->default(0);
                $table->string('bill_type')->default('regular');
                $table->string('pan_number')->nullable();
                $table->string('payment_status')->default('pending');
                $table->date('payment_due_date')->nullable();
                
                // Status
                $table->string('status')->default('pending');
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                
                // Admin margin
                $table->decimal('admin_margin', 10, 2)->default(0);
                
                $table->timestamps();
                
                $table->index('client_id');
                $table->index('driver_id');
                $table->index('status');
                $table->index('tracking_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pickup_requests');
    }
};