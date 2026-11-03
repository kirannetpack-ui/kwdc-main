<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_rates', function (Blueprint $table) {
            // Add user_id column (same as driver_id for compatibility)
            if (!Schema::hasColumn('driver_rates', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            
            // Add date column
            if (!Schema::hasColumn('driver_rates', 'date')) {
                $table->date('date')->nullable()->after('valid_until');
            }
            
            // Add missing columns for compatibility
            if (!Schema::hasColumn('driver_rates', 'vehicle_type')) {
                $table->string('vehicle_type')->nullable()->after('user_id');
            }
            
            if (!Schema::hasColumn('driver_rates', 'base_fare')) {
                $table->decimal('base_fare', 10, 2)->default(0)->after('rate_per_km_21_plus');
            }
            
            if (!Schema::hasColumn('driver_rates', 'minimum_fare')) {
                $table->decimal('minimum_fare', 10, 2)->default(0)->after('base_fare');
            }
            
            if (!Schema::hasColumn('driver_rates', 'notes')) {
                $table->text('notes')->nullable()->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_rates', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'date', 'vehicle_type', 'base_fare', 'minimum_fare', 'notes']);
        });
    }
};