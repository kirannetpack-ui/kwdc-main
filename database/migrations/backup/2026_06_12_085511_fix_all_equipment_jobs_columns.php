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
                // Owner and client relations
                if (!Schema::hasColumn('equipment_jobs', 'owner_id')) {
                    $table->foreignId('owner_id')->nullable()->constrained('users');
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'client_id')) {
                    $table->foreignId('client_id')->nullable()->constrained('users');
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'equipment_id')) {
                    $table->foreignId('equipment_id')->nullable()->constrained('equipment');
                }
                
                // Job details
                if (!Schema::hasColumn('equipment_jobs', 'job_type')) {
                    $table->string('job_type', 50)->nullable();
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'pickup_location')) {
                    $table->text('pickup_location')->nullable();
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'delivery_location')) {
                    $table->text('delivery_location')->nullable();
                }
                
                // Pricing
                if (!Schema::hasColumn('equipment_jobs', 'price')) {
                    $table->decimal('price', 10, 2)->default(0);
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'proposed_price')) {
                    $table->decimal('proposed_price', 10, 2)->nullable();
                }
                
                // Status fields
                if (!Schema::hasColumn('equipment_jobs', 'accepted_by_client_status')) {
                    $table->string('accepted_by_client_status', 50)->nullable();
                }
                
                if (!Schema::hasColumn('equipment_jobs', 'accepted_by_owner_status')) {
                    $table->string('accepted_by_owner_status', 50)->nullable();
                }
                
                // Timestamps
                if (!Schema::hasColumn('equipment_jobs', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('equipment_jobs')) {
            Schema::table('equipment_jobs', function (Blueprint $table) {
                $columns = [
                    'owner_id', 'client_id', 'equipment_id', 'job_type',
                    'pickup_location', 'delivery_location', 'price',
                    'proposed_price', 'accepted_by_client_status',
                    'accepted_by_owner_status', 'completed_at'
                ];
                
                foreach ($columns as $column) {
                    if (Schema::hasColumn('equipment_jobs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};