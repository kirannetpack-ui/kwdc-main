<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Fix dispatch_orders table
        if (Schema::hasTable('dispatch_orders')) {
            if (!Schema::hasColumn('dispatch_orders', 'accepted_by_client_status')) {
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->string('accepted_by_client_status', 50)->nullable();
                });
            }
        }
        
        // Fix equipment_jobs table - handle typo
        if (Schema::hasTable('equipment_jobs')) {
            // Fix typo if exists
            if (Schema::hasColumn('equipment_jobs', 'acepted_by_client_status')) {
                Schema::table('equipment_jobs', function (Blueprint $table) {
                    $table->renameColumn('acepted_by_client_status', 'accepted_by_client_status');
                });
            }
            
            // Add if missing
            if (!Schema::hasColumn('equipment_jobs', 'accepted_by_client_status')) {
                Schema::table('equipment_jobs', function (Blueprint $table) {
                    $table->string('accepted_by_client_status', 50)->nullable();
                });
            }
        }
        
        // Check other common tables
        $tables = ['pickup_requests', 'warehouse_requests', 'dispatches'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                if (!Schema::hasColumn($table, 'accepted_by_client_status')) {
                    Schema::table($table, function (Blueprint $table) {
                        $table->string('accepted_by_client_status', 50)->nullable();
                    });
                }
            }
        }
    }

    public function down()
    {
        // No need to rollback as these are safe checks
    }
};