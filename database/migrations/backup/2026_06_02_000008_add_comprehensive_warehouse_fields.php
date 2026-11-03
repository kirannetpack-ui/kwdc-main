<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            // Warehouse Type
            if (!Schema::hasColumn('warehouses', 'warehouse_type')) {
                $table->enum('warehouse_type', ['building', 'plot_land', 'cold_storage'])->default('building');
            }
            
            // Area Measurements
            if (!Schema::hasColumn('warehouses', 'total_area_sqft')) {
                $table->decimal('total_area_sqft', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'total_area_sqm')) {
                $table->decimal('total_area_sqm', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'built_up_area')) {
                $table->decimal('built_up_area', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'open_area')) {
                $table->decimal('open_area', 12, 2)->nullable();
            }
            
            // Dimensions
            if (!Schema::hasColumn('warehouses', 'length_ft')) {
                $table->decimal('length_ft', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'width_ft')) {
                $table->decimal('width_ft', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'height_ft')) {
                $table->decimal('height_ft', 10, 2)->nullable();
            }
            
            // Security Features
            if (!Schema::hasColumn('warehouses', 'cctv_count')) {
                $table->integer('cctv_count')->default(0);
            }
            if (!Schema::hasColumn('warehouses', 'security_guard_count')) {
                $table->integer('security_guard_count')->default(0);
            }
            if (!Schema::hasColumn('warehouses', 'fire_extinguisher_count')) {
                $table->integer('fire_extinguisher_count')->default(0);
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
            
            // Access & Infrastructure
            if (!Schema::hasColumn('warehouses', 'road_access_type')) {
                $table->string('road_access_type')->nullable(); // paved, gravel, dirt
            }
            if (!Schema::hasColumn('warehouses', 'vehicle_access_height')) {
                $table->decimal('vehicle_access_height', 5, 2)->nullable(); // in feet
            }
            if (!Schema::hasColumn('warehouses', 'nearest_highway_distance')) {
                $table->decimal('nearest_highway_distance', 8, 2)->nullable(); // in km
            }
            
            // Cold Storage Specific
            if (!Schema::hasColumn('warehouses', 'temperature_range_min')) {
                $table->decimal('temperature_range_min', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'temperature_range_max')) {
                $table->decimal('temperature_range_max', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'has_humidity_control')) {
                $table->boolean('has_humidity_control')->default(false);
            }
            if (!Schema::hasColumn('warehouses', 'has_backup_cooling')) {
                $table->boolean('has_backup_cooling')->default(false);
            }
            
            // Nearby Facilities (Enhanced)
            if (!Schema::hasColumn('warehouses', 'nearest_bank')) {
                $table->string('nearest_bank')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'nearest_fuel_station')) {
                $table->string('nearest_fuel_station')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'nearest_restaurant')) {
                $table->string('nearest_restaurant')->nullable();
            }
            
            // Document Uploads
            if (!Schema::hasColumn('warehouses', 'ownership_document')) {
                $table->string('ownership_document')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'tax_clearance_document')) {
                $table->string('tax_clearance_document')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'fire_safety_certificate')) {
                $table->string('fire_safety_certificate')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'building_approval_document')) {
                $table->string('building_approval_document')->nullable();
            }
            
            // Photos
            if (!Schema::hasColumn('warehouses', 'front_photo')) {
                $table->string('front_photo')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'interior_photo_1')) {
                $table->string('interior_photo_1')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'interior_photo_2')) {
                $table->string('interior_photo_2')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'interior_photo_3')) {
                $table->string('interior_photo_3')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'exterior_photo_1')) {
                $table->string('exterior_photo_1')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'exterior_photo_2')) {
                $table->string('exterior_photo_2')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'security_room_photo')) {
                $table->string('security_room_photo')->nullable();
            }
            
            // Free Text Fields
            if (!Schema::hasColumn('warehouses', 'special_features')) {
                $table->text('special_features')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'restrictions')) {
                $table->text('restrictions')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'additional_notes')) {
                $table->text('additional_notes')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn([
                'warehouse_type', 'total_area_sqft', 'total_area_sqm', 'built_up_area', 'open_area',
                'length_ft', 'width_ft', 'height_ft', 'cctv_count', 'security_guard_count',
                'fire_extinguisher_count', 'has_fire_alarm', 'has_sprinkler_system', 'has_generator_backup',
                'has_loading_dock', 'road_access_type', 'vehicle_access_height', 'nearest_highway_distance',
                'temperature_range_min', 'temperature_range_max', 'has_humidity_control', 'has_backup_cooling',
                'nearest_bank', 'nearest_fuel_station', 'nearest_restaurant', 'ownership_document',
                'tax_clearance_document', 'fire_safety_certificate', 'building_approval_document',
                'front_photo', 'interior_photo_1', 'interior_photo_2', 'interior_photo_3',
                'exterior_photo_1', 'exterior_photo_2', 'security_room_photo', 'special_features',
                'restrictions', 'additional_notes'
            ]);
        });
    }
};