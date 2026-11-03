<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('driver_rates')) {
            // Add driver_id column if missing
            if (!Schema::hasColumn('driver_rates', 'driver_id')) {
                Schema::table('driver_rates', function (Blueprint $table) {
                    $table->foreignId('driver_id')->nullable()->after('user_id');
                });
            }
            
            // Add is_active column if missing
            if (!Schema::hasColumn('driver_rates', 'is_active')) {
                Schema::table('driver_rates', function (Blueprint $table) {
                    $table->boolean('is_active')->default(true)->after('status');
                });
            }
            
            // Add valid_until column if missing
            if (!Schema::hasColumn('driver_rates', 'valid_until')) {
                Schema::table('driver_rates', function (Blueprint $table) {
                    $table->timestamp('valid_until')->nullable()->after('is_active');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('driver_rates')) {
            $columns = ['driver_id', 'is_active', 'valid_until'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('driver_rates', $column)) {
                    Schema::table('driver_rates', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};