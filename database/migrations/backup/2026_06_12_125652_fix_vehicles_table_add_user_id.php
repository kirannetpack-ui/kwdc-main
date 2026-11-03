<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('vehicles')) {
            // Check if user_id column exists
            if (!Schema::hasColumn('vehicles', 'user_id')) {
                Schema::table('vehicles', function (Blueprint $table) {
                    $table->foreignId('user_id')->nullable()->after('id');
                });
            }
            
            // Check if driver_id column exists (as alternative)
            if (!Schema::hasColumn('vehicles', 'driver_id')) {
                Schema::table('vehicles', function (Blueprint $table) {
                    $table->foreignId('driver_id')->nullable()->after('user_id');
                });
            }
            
            // Add other missing columns
            if (!Schema::hasColumn('vehicles', 'vehicle_number')) {
                Schema::table('vehicles', function (Blueprint $table) {
                    $table->string('vehicle_number')->nullable()->after('driver_id');
                });
            }
            
            if (!Schema::hasColumn('vehicles', 'vehicle_type')) {
                Schema::table('vehicles', function (Blueprint $table) {
                    $table->string('vehicle_type', 50)->default('Standard')->after('vehicle_number');
                });
            }
            
            if (!Schema::hasColumn('vehicles', 'status')) {
                Schema::table('vehicles', function (Blueprint $table) {
                    $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active')->after('vehicle_type');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('vehicles')) {
            $columns = ['user_id', 'driver_id', 'vehicle_number', 'vehicle_type', 'status'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('vehicles', $column)) {
                    Schema::table('vehicles', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};