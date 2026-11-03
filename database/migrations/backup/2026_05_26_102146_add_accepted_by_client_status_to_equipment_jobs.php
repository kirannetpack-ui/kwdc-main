<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('equipment_jobs')) {
            // Fix: Correct column name from 'acepted_by_client_status' to 'accepted_by_client_status'
            if (Schema::hasColumn('equipment_jobs', 'acepted_by_client_status')) {
                Schema::table('equipment_jobs', function (Blueprint $table) {
                    $table->renameColumn('acepted_by_client_status', 'accepted_by_client_status');
                });
            }
            
            // If column doesn't exist at all, add it
            if (!Schema::hasColumn('equipment_jobs', 'accepted_by_client_status')) {
                Schema::table('equipment_jobs', function (Blueprint $table) {
                    $table->string('accepted_by_client_status', 50)->nullable()->after('status');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('equipment_jobs')) {
            if (Schema::hasColumn('equipment_jobs', 'accepted_by_client_status')) {
                Schema::table('equipment_jobs', function (Blueprint $table) {
                    $table->dropColumn('accepted_by_client_status');
                });
            }
        }
    }
};