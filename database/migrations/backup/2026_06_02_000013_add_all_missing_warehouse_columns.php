<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            // Description
            if (!Schema::hasColumn('warehouses', 'description')) {
                $table->text('description')->nullable();
            }
            
            // Price Unit
            if (!Schema::hasColumn('warehouses', 'price_unit')) {
                $table->string('price_unit')->default('sqft');
            }
            
            // Total area
            if (!Schema::hasColumn('warehouses', 'total_area')) {
                $table->decimal('total_area', 12, 2)->nullable();
            }
            
            // Built up area
            if (!Schema::hasColumn('warehouses', 'built_up_area')) {
                $table->decimal('built_up_area', 12, 2)->nullable();
            }
            
            // Open area
            if (!Schema::hasColumn('warehouses', 'open_area')) {
                $table->decimal('open_area', 12, 2)->nullable();
            }
            
            // Security features
            if (!Schema::hasColumn('warehouses', 'security_features')) {
                $table->text('security_features')->nullable();
            }
            
            // Contact fields
            if (!Schema::hasColumn('warehouses', 'contact_person')) {
                $table->string('contact_person')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'contact_phone')) {
                $table->string('contact_phone')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'contact_email')) {
                $table->string('contact_email')->nullable();
            }
            
            // Operating hours
            if (!Schema::hasColumn('warehouses', 'operating_hours')) {
                $table->string('operating_hours')->nullable();
            }
            
            // Access restrictions
            if (!Schema::hasColumn('warehouses', 'access_restrictions')) {
                $table->text('access_restrictions')->nullable();
            }
            
            // Documents required
            if (!Schema::hasColumn('warehouses', 'documents_required')) {
                $table->text('documents_required')->nullable();
            }
            
            // Special features
            if (!Schema::hasColumn('warehouses', 'special_features')) {
                $table->text('special_features')->nullable();
            }
            
            // Restrictions
            if (!Schema::hasColumn('warehouses', 'restrictions')) {
                $table->text('restrictions')->nullable();
            }
            
            // Additional notes
            if (!Schema::hasColumn('warehouses', 'additional_notes')) {
                $table->text('additional_notes')->nullable();
            }
            
            // Document uploads
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
            
            // Photo uploads
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
            
            // Security booleans
            if (!Schema::hasColumn('warehouses', 'cctv_available')) {
                $table->boolean('cctv_available')->default(false);
            }
            if (!Schema::hasColumn('warehouses', 'security_guard')) {
                $table->boolean('security_guard')->default(false);
            }
            if (!Schema::hasColumn('warehouses', 'fire_safety')) {
                $table->boolean('fire_safety')->default(false);
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
            if (!Schema::hasColumn('warehouses', 'has_humidity_control')) {
                $table->boolean('has_humidity_control')->default(false);
            }
            if (!Schema::hasColumn('warehouses', 'has_backup_cooling')) {
                $table->boolean('has_backup_cooling')->default(false);
            }
            
            // Count fields
            if (!Schema::hasColumn('warehouses', 'cctv_count')) {
                $table->integer('cctv_count')->default(0);
            }
            if (!Schema::hasColumn('warehouses', 'security_guard_count')) {
                $table->integer('security_guard_count')->default(0);
            }
            if (!Schema::hasColumn('warehouses', 'fire_extinguisher_count')) {
                $table->integer('fire_extinguisher_count')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'price_unit', 'total_area', 'built_up_area', 'open_area',
                'security_features', 'contact_person', 'contact_phone', 'contact_email',
                'operating_hours', 'access_restrictions', 'documents_required',
                'special_features', 'restrictions', 'additional_notes',
                'ownership_document', 'tax_clearance_document', 'fire_safety_certificate',
                'building_approval_document', 'front_photo', 'interior_photo_1',
                'interior_photo_2', 'interior_photo_3', 'exterior_photo_1',
                'exterior_photo_2', 'security_room_photo', 'cctv_available',
                'security_guard', 'fire_safety', 'has_fire_alarm', 'has_sprinkler_system',
                'has_generator_backup', 'has_loading_dock', 'emergency_exit',
                'has_humidity_control', 'has_backup_cooling', 'cctv_count',
                'security_guard_count', 'fire_extinguisher_count'
            ]);
        });
    }
};