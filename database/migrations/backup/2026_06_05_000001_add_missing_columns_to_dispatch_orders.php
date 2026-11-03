<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Skip if table doesn't exist
        if (!Schema::hasTable('dispatch_orders')) {
            return;
        }
        
        Schema::table('dispatch_orders', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('dispatch_orders', 'admin_margin')) {
                $table->decimal('admin_margin', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'driver_earning')) {
                $table->decimal('driver_earning', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'pickup_latitude')) {
                $table->decimal('pickup_latitude', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'pickup_longitude')) {
                $table->decimal('pickup_longitude', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'delivery_latitude')) {
                $table->decimal('delivery_latitude', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'delivery_longitude')) {
                $table->decimal('delivery_longitude', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'distance_km')) {
                $table->decimal('distance_km', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'base_price')) {
                $table->decimal('base_price', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'dispatch_number')) {
                $table->string('dispatch_number')->unique()->nullable();
            }
        });
    }

    public function down()
    {
        // No down method needed
    }
};