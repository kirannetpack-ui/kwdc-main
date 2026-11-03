<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('dispatch_orders', 'tracking_enabled')) {
                $table->boolean('tracking_enabled')->default(false);
            }
            if (!Schema::hasColumn('dispatch_orders', 'tracking_started_at')) {
                $table->timestamp('tracking_started_at')->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'current_latitude')) {
                $table->decimal('current_latitude', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'current_longitude')) {
                $table->decimal('current_longitude', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'last_location_update')) {
                $table->timestamp('last_location_update')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_enabled', 'tracking_started_at',
                'current_latitude', 'current_longitude', 'last_location_update'
            ]);
        });
    }
};