<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('pickup_requests', 'pickup_address')) {
                $table->text('pickup_address')->nullable()->after('invoice_no');
            }
            if (!Schema::hasColumn('pickup_requests', 'destination_address')) {
                $table->text('destination_address')->nullable()->after('pickup_address');
            }
            if (!Schema::hasColumn('pickup_requests', 'pickup_latitude')) {
                $table->decimal('pickup_latitude', 10, 8)->nullable()->after('destination_address');
            }
            if (!Schema::hasColumn('pickup_requests', 'pickup_longitude')) {
                $table->decimal('pickup_longitude', 11, 8)->nullable()->after('pickup_latitude');
            }
            if (!Schema::hasColumn('pickup_requests', 'destination_latitude')) {
                $table->decimal('destination_latitude', 10, 8)->nullable()->after('pickup_longitude');
            }
            if (!Schema::hasColumn('pickup_requests', 'destination_longitude')) {
                $table->decimal('destination_longitude', 11, 8)->nullable()->after('destination_latitude');
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
                $table->time('scheduled_time')->nullable()->after('scheduled_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            foreach ([
                'scheduled_time',
                'scheduled_date',
                'weight',
                'items_description',
                'destination_longitude',
                'destination_latitude',
                'pickup_longitude',
                'pickup_latitude',
                'destination_address',
                'pickup_address',
            ] as $column) {
                if (Schema::hasColumn('pickup_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
