<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Only add columns if the table exists
        if (Schema::hasTable('pickup_requests')) {
            Schema::table('pickup_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('pickup_requests', 'admin_margin')) {
                    $table->decimal('admin_margin', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('pickup_requests', 'driver_earning')) {
                    $table->decimal('driver_earning', 10, 2)->nullable();
                }
            });
        }
        
        // Create pickup_stops table if it doesn't exist
        if (!Schema::hasTable('pickup_stops')) {
            Schema::create('pickup_stops', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pickup_request_id')->constrained('pickup_requests')->onDelete('cascade');
                $table->integer('stop_order');
                $table->string('contact_name');
                $table->string('contact_phone');
                $table->text('address');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->integer('boxes_count')->default(0);
                $table->text('notes')->nullable();
                $table->string('invoice_number')->nullable();
                $table->string('invoice_document')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // No down method
    }
};