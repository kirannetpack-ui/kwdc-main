<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_rates', function (Blueprint $table) {
            // Check if user_id column exists
            if (!Schema::hasColumn('driver_rates', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            
            // Check if driver_id column exists
            if (!Schema::hasColumn('driver_rates', 'driver_id')) {
                $table->unsignedBigInteger('driver_id')->nullable()->after('user_id');
                $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            }
            
            // Check if is_active column exists
            if (!Schema::hasColumn('driver_rates', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('valid_until');
            }
            
            // Check if date column exists
            if (!Schema::hasColumn('driver_rates', 'date')) {
                $table->date('date')->nullable()->after('valid_until');
            }
            
            // Check if valid_until column exists
            if (!Schema::hasColumn('driver_rates', 'valid_until')) {
                $table->timestamp('valid_until')->nullable()->after('date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_rates', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['driver_id']);
            $table->dropColumn(['user_id', 'driver_id', 'is_active', 'date', 'valid_until']);
        });
    }
};