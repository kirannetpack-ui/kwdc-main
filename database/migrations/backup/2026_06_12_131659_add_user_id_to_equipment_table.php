<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('equipment')) {
            // Add user_id column if missing
            if (!Schema::hasColumn('equipment', 'user_id')) {
                Schema::table('equipment', function (Blueprint $table) {
                    $table->foreignId('user_id')->nullable()->after('id');
                });
            }
            
            // Add owner_id column if missing (alias)
            if (!Schema::hasColumn('equipment', 'owner_id')) {
                Schema::table('equipment', function (Blueprint $table) {
                    $table->foreignId('owner_id')->nullable()->after('user_id');
                });
            }
            
            // Add status column if missing
            if (!Schema::hasColumn('equipment', 'status')) {
                Schema::table('equipment', function (Blueprint $table) {
                    $table->string('status', 50)->default('available')->after('description');
                });
            }
            
            // Add equipment_name column if missing
            if (!Schema::hasColumn('equipment', 'equipment_name')) {
                Schema::table('equipment', function (Blueprint $table) {
                    $table->string('equipment_name')->nullable()->after('user_id');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('equipment')) {
            $columns = ['user_id', 'owner_id', 'status', 'equipment_name'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('equipment', $column)) {
                    Schema::table('equipment', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};