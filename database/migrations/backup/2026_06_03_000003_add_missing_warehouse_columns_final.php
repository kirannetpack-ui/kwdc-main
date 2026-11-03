<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            // Area columns
            if (!Schema::hasColumn('warehouses', 'usable_area')) {
                $table->decimal('usable_area', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'built_up_area_sqm')) {
                $table->decimal('built_up_area_sqm', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'open_area_sqm')) {
                $table->decimal('open_area_sqm', 12, 2)->nullable();
            }
            
            // Security columns
            if (!Schema::hasColumn('warehouses', 'cctv_urls')) {
                $table->text('cctv_urls')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'has_fire_alarm')) {
                $table->boolean('has_fire_alarm')->default(false);
            }
            if (!Schema::hasColumn('warehouses', 'has_sprinkler_system')) {
                $table->boolean('has_sprinkler_system')->default(false);
            }
            if (!Schema::hasColumn('warehouses', 'has_generator_backup')) {
                $table->boolean('has_generator_backup')->default(false);
            }
            if (!Schema::hasColumn('warehouses', 'has_loading_dock')) {
                $table->boolean('has_loading_dock')->default(false);
            }
            if (!Schema::hasColumn('warehouses', 'emergency_exit')) {
                $table->boolean('emergency_exit')->default(false);
            }
            if (!Schema::hasColumn('warehouses', 'fire_safety')) {
                $table->boolean('fire_safety')->default(false);
            }
            if (!Schema::hasColumn('warehouses', 'has_humidity_control')) {
                $table->boolean('has_humidity_control')->default(false);
            }
            if (!Schema::hasColumn('warehouses', 'has_backup_cooling')) {
                $table->boolean('has_backup_cooling')->default(false);
            }
            
            // Access columns
            if (!Schema::hasColumn('warehouses', 'is_motorable')) {
                $table->boolean('is_motorable')->default(false);
            }
            if (!Schema::hasColumn('warehouses', 'road_access_type')) {
                $table->string('road_access_type')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'vehicle_access_height')) {
                $table->decimal('vehicle_access_height', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'nearest_highway_distance')) {
                $table->decimal('nearest_highway_distance', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'distance_from_city')) {
                $table->decimal('distance_from_city', 8, 2)->nullable();
            }
            
            // Temperature columns
            if (!Schema::hasColumn('warehouses', 'temperature_range_min')) {
                $table->decimal('temperature_range_min', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'temperature_range_max')) {
                $table->decimal('temperature_range_max', 5, 2)->nullable();
            }
            
            // Nearby facilities columns
            if (!Schema::hasColumn('warehouses', 'nearest_restaurant')) {
                $table->string('nearest_restaurant')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'nearest_bank')) {
                $table->string('nearest_bank')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'nearest_fuel_station')) {
                $table->string('nearest_fuel_station')->nullable();
            }
            
            // Additional columns
            if (!Schema::hasColumn('warehouses', 'special_features')) {
                $table->text('special_features')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'restrictions')) {
                $table->text('restrictions')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'additional_notes')) {
                $table->text('additional_notes')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'allow_shared')) {
                $table->boolean('allow_shared')->default(true);
            }
            
            // Contact columns
            if (!Schema::hasColumn('warehouses', 'contact_email')) {
                $table->string('contact_email')->nullable();
            }
            
            // Document columns
            if (!Schema::hasColumn('warehouses', 'fire_safety_certificate')) {
                $table->string('fire_safety_certificate')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'building_approval_document')) {
                $table->string('building_approval_document')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn([
                'usable_area', 'built_up_area_sqm', 'open_area_sqm', 'cctv_urls',
                'has_fire_alarm', 'has_sprinkler_system', 'has_generator_backup',
                'has_loading_dock', 'emergency_exit', 'fire_safety',
                'has_humidity_control', 'has_backup_cooling', 'is_motorable',
                'road_access_type', 'vehicle_access_height', 'nearest_highway_distance',
                'distance_from_city', 'temperature_range_min', 'temperature_range_max',
                'nearest_restaurant', 'nearest_bank', 'nearest_fuel_station',
                'special_features', 'restrictions', 'additional_notes', 'allow_shared',
                'contact_email', 'fire_safety_certificate', 'building_approval_document'
            ]);
        });
    }
};