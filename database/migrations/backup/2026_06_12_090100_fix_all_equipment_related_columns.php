<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Fix equipment_jobs table
        if (Schema::hasTable('equipment_jobs')) {
            Schema::table('equipment_jobs', function (Blueprint $table) {
                // Add equipment_owner_id
                if (!Schema::hasColumn('equipment_jobs', 'equipment_owner_id')) {
                    $table->foreignId('equipment_owner_id')->nullable()->constrained('users');
                }
                
                // Add owner_id if missing (alias)
                if (!Schema::hasColumn('equipment_jobs', 'owner_id')) {
                    $table->foreignId('owner_id')->nullable()->constrained('users');
                }
                
                // Add client_id
                if (!Schema::hasColumn('equipment_jobs', 'client_id')) {
                    $table->foreignId('client_id')->nullable()->constrained('users');
                }
                
                // Add price columns
                if (!Schema::hasColumn('equipment_jobs', 'price')) {
                    $table->decimal('price', 10, 2)->default(0);
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'proposed_price')) {
                    $table->decimal('proposed_price', 10, 2)->nullable();
                }
                
                // Add status columns
                if (!Schema::hasColumn('equipment_jobs', 'accepted_by_client_status')) {
                    $table->string('accepted_by_client_status', 50)->nullable();
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'accepted_by_owner_status')) {
                    $table->string('accepted_by_owner_status', 50)->nullable();
                }
                
                // Add dates
                if (!Schema::hasColumn('equipment_jobs', 'request_date')) {
                    $table->date('request_date')->nullable();
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'completion_date')) {
                    $table->date('completion_date')->nullable();
                }
            });
        }
        
        // Fix equipment table
        if (Schema::hasTable('equipment')) {
            Schema::table('equipment', function (Blueprint $table) {
                if (!Schema::hasColumn('equipment', 'equipment_owner_id')) {
                    $table->foreignId('equipment_owner_id')->nullable()->constrained('users');
                }
                
                if (!Schema::hasColumn('equipment', 'owner_id')) {
                    $table->foreignId('owner_id')->nullable()->constrained('users');
                }
                
                if (!Schema::hasColumn('equipment', 'daily_rate')) {
                    $table->decimal('daily_rate', 10, 2)->default(0);
                }
                
                if (!Schema::hasColumn('equipment', 'availability_status')) {
                    $table->string('availability_status', 50)->default('available');
                }
            });
        }
    }

    public function down()
    {
        // Remove columns if needed
        if (Schema::hasTable('equipment_jobs')) {
            Schema::table('equipment_jobs', function (Blueprint $table) {
                $columns = ['equipment_owner_id', 'request_date', 'completion_date'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('equipment_jobs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
        
        if (Schema::hasTable('equipment')) {
            Schema::table('equipment', function (Blueprint $table) {
                $columns = ['equipment_owner_id', 'daily_rate', 'availability_status'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('equipment', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};