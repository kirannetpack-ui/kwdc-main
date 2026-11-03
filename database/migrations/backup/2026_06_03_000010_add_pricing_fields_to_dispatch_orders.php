<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            // Add columns one by one, checking if they exist
            if (!Schema::hasColumn('dispatch_orders', 'driver_rate_id')) {
                $table->foreignId('driver_rate_id')->nullable();
            }
            
            if (!Schema::hasColumn('dispatch_orders', 'distance_km')) {
                $table->decimal('distance_km', 10, 2)->nullable();
            }
            
            if (!Schema::hasColumn('dispatch_orders', 'base_price')) {
                $table->decimal('base_price', 12, 2)->nullable();
            }
            
            if (!Schema::hasColumn('dispatch_orders', 'admin_margin')) {
                $table->decimal('admin_margin', 12, 2)->nullable();
            }
            
            if (!Schema::hasColumn('dispatch_orders', 'driver_earning')) {
                $table->decimal('driver_earning', 12, 2)->nullable();
            }
        });

        // Add foreign key only if both tables exist and column exists
        if (Schema::hasTable('driver_rates') && Schema::hasColumn('dispatch_orders', 'driver_rate_id')) {
            try {
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->foreign('driver_rate_id')
                          ->references('id')
                          ->on('driver_rates')
                          ->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Skip if foreign key already exists
            }
        }
    }

    public function down()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            try {
                $table->dropForeign(['driver_rate_id']);
            } catch (\Exception $e) {}
            
            $columns = ['driver_rate_id', 'admin_margin', 'driver_earning', 'distance_km', 'base_price'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('dispatch_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};