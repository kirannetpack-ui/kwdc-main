<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if dispatch_orders table exists
        if (Schema::hasTable('dispatch_orders')) {
            
            // First check if column exists, if not then add it
            if (!Schema::hasColumn('dispatch_orders', 'accepted_by_client_status')) {
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->string('accepted_by_client_status', 50)->nullable()->after('status');
                });
            } else {
                // If column exists, then modify it
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->string('accepted_by_client_status', 50)->nullable()->change();
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('dispatch_orders')) {
            if (Schema::hasColumn('dispatch_orders', 'accepted_by_client_status')) {
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->dropColumn('accepted_by_client_status');
                });
            }
        }
    }
};