<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('equipment_jobs')) {
            Schema::table('equipment_jobs', function (Blueprint $table) {
                // Add missing columns safely
                if (!Schema::hasColumn('equipment_jobs', 'owner_id')) {
                    $table->foreignId('owner_id')->nullable()->constrained('users');
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'equipment_id')) {
                    $table->foreignId('equipment_id')->nullable()->constrained('equipment');
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'client_id')) {
                    $table->foreignId('client_id')->nullable()->constrained('users');
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'job_type')) {
                    $table->string('job_type')->nullable();
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'pickup_location')) {
                    $table->text('pickup_location')->nullable();
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'delivery_location')) {
                    $table->text('delivery_location')->nullable();
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'price')) {
                    $table->decimal('price', 10, 2)->default(0);
                }
            });
        }
    }

    public function down()
    {
        // Define down method if needed
    }
};