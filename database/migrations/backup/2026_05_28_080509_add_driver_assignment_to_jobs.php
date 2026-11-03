<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add assigned_driver_id to dispatch_orders
        Schema::table('dispatch_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('dispatch_orders', 'assigned_driver_id')) {
                $table->foreignId('assigned_driver_id')->nullable()->constrained('users');
            }
        });

        // Add assigned_driver_id to pickup_requests
        Schema::table('pickup_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('pickup_requests', 'assigned_driver_id')) {
                $table->foreignId('assigned_driver_id')->nullable()->constrained('users');
            }
        });
    }

    public function down()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->dropForeign(['assigned_driver_id']);
            $table->dropColumn('assigned_driver_id');
        });
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropForeign(['assigned_driver_id']);
            $table->dropColumn('assigned_driver_id');
        });
    }
};