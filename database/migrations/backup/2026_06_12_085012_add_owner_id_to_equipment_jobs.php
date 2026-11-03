<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('equipment_jobs')) {
            if (!Schema::hasColumn('equipment_jobs', 'owner_id')) {
                Schema::table('equipment_jobs', function (Blueprint $table) {
                    $table->foreignId('owner_id')->nullable()->constrained('users')->after('id');
                });
            }
            
            if (!Schema::hasColumn('equipment_jobs', 'equipment_id')) {
                Schema::table('equipment_jobs', function (Blueprint $table) {
                    $table->foreignId('equipment_id')->nullable()->constrained('equipment')->after('owner_id');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('equipment_jobs')) {
            if (Schema::hasColumn('equipment_jobs', 'owner_id')) {
                Schema::table('equipment_jobs', function (Blueprint $table) {
                    $table->dropForeign(['owner_id']);
                    $table->dropColumn('owner_id');
                });
            }
        }
    }
};