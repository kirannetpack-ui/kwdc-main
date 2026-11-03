<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            // Add missing timestamp columns
            if (!Schema::hasColumn('pickup_requests', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('pickup_requests', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('assigned_at');
            }
            
            if (!Schema::hasColumn('pickup_requests', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }
            
            if (!Schema::hasColumn('pickup_requests', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            }
            
            // Add missing columns
            if (!Schema::hasColumn('pickup_requests', 'pickup_address')) {
                $table->text('pickup_address')->nullable()->after('driver_id');
            }
            
            if (!Schema::hasColumn('pickup_requests', 'destination_address')) {
                $table->text('destination_address')->nullable()->after('pickup_address');
            }
            
            if (!Schema::hasColumn('pickup_requests', 'pickup_latitude')) {
                $table->decimal('pickup_latitude', 10, 8)->nullable()->after('destination_address');
            }
            
            if (!Schema::hasColumn('pickup_requests', 'pickup_longitude')) {
                $table->decimal('pickup_longitude', 10, 8)->nullable()->after('pickup_latitude');
            }
            
            if (!Schema::hasColumn('pickup_requests', 'items_description')) {
                $table->text('items_description')->nullable()->after('destination_longitude');
            }
            
            if (!Schema::hasColumn('pickup_requests', 'weight')) {
                $table->decimal('weight', 10, 2)->nullable()->after('items_description');
            }
            
            if (!Schema::hasColumn('pickup_requests', 'scheduled_date')) {
                $table->date('scheduled_date')->nullable()->after('weight');
            }
            
            if (!Schema::hasColumn('pickup_requests', 'scheduled_time')) {
                $table->string('scheduled_time')->nullable()->after('scheduled_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropColumn([
                'assigned_at',
                'started_at',
                'completed_at',
                'cancelled_at',
                'pickup_address',
                'destination_address',
                'pickup_latitude',
                'pickup_longitude',
                'items_description',
                'weight',
                'scheduled_date',
                'scheduled_time'
            ]);
        });
    }
};