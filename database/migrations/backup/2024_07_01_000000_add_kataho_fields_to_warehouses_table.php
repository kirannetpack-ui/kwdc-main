<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add Kataho fields to warehouses table
        Schema::table('warehouses', function (Blueprint $table) {
            // Kataho location fields
            if (!Schema::hasColumn('warehouses', 'kataho_code')) {
                $table->string('kataho_code')->nullable()->after('longitude');
                $table->string('kataho_grid_id')->nullable()->after('kataho_code');
                $table->string('kataho_plate_id')->nullable()->after('kataho_grid_id');
                $table->text('kataho_address')->nullable()->after('kataho_plate_id');
                $table->boolean('kataho_verified')->default(false)->after('kataho_address');
                $table->timestamp('kataho_verified_at')->nullable()->after('kataho_verified');
            }
        });
        
        // Add Kataho fields to users table (if needed)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'kataho_code')) {
                $table->string('kataho_code')->nullable()->after('remember_token');
                $table->string('kataho_grid_id')->nullable()->after('kataho_code');
                $table->text('kataho_address')->nullable()->after('kataho_grid_id');
                $table->boolean('kataho_verified')->default(false)->after('kataho_address');
            }
        });
        
        // NOTE: kataho_locations table is created in a separate migration
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn([
                'kataho_code',
                'kataho_grid_id',
                'kataho_plate_id',
                'kataho_address',
                'kataho_verified',
                'kataho_verified_at'
            ]);
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kataho_code',
                'kataho_grid_id',
                'kataho_address',
                'kataho_verified'
            ]);
        });
    }
};