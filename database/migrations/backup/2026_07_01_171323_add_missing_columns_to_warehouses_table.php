<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('warehouses', 'area_sqft')) {
                $table->decimal('area_sqft', 10, 2)->nullable()->after('longitude');
            }
            
            if (!Schema::hasColumn('warehouses', 'area_sqm')) {
                $table->decimal('area_sqm', 10, 2)->nullable()->after('area_sqft');
            }
            
            if (!Schema::hasColumn('warehouses', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            
            if (!Schema::hasColumn('warehouses', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            
            if (!Schema::hasColumn('warehouses', 'country')) {
                $table->string('country')->nullable()->after('state');
            }
            
            if (!Schema::hasColumn('warehouses', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('country');
            }
            
            if (!Schema::hasColumn('warehouses', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('area_sqm');
            }
            
            if (!Schema::hasColumn('warehouses', 'description')) {
                $table->text('description')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn([
                'area_sqft',
                'area_sqm',
                'city',
                'state',
                'country',
                'postal_code',
                'price',
                'description'
            ]);
        });
    }
};