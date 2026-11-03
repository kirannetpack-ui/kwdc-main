<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if table already exists before creating
        if (!Schema::hasTable('vehicles')) {
            Schema::create('vehicles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
                $table->string('driver_code')->nullable();
                $table->string('vehicle_number')->unique();
                $table->string('vehicle_type');
                $table->string('custom_vehicle_type')->nullable();
                $table->decimal('capacity', 10, 2);
                $table->string('capacity_unit')->default('kg');
                $table->string('manufacturer')->nullable();
                $table->string('model')->nullable();
                $table->integer('year')->nullable();
                $table->string('color')->nullable();
                $table->string('fuel_type')->nullable();
                $table->string('registration_number')->unique();
                $table->date('registration_date')->nullable();
                $table->string('insurance_number')->nullable();
                $table->date('insurance_valid_until')->nullable();
                $table->string('insurance_file_path')->nullable();
                $table->string('fitness_certificate_number')->nullable();
                $table->date('fitness_valid_until')->nullable();
                $table->string('fitness_file_path')->nullable();
                $table->string('pollution_certificate_number')->nullable();
                $table->date('pollution_valid_until')->nullable();
                $table->string('pollution_file_path')->nullable();
                $table->string('permit_number')->nullable();
                $table->date('permit_valid_until')->nullable();
                $table->string('permit_file_path')->nullable();
                $table->string('blue_book_number')->nullable();
                $table->string('blue_book_file_path')->nullable();
                $table->string('front_photo_path')->nullable();
                $table->string('back_photo_path')->nullable();
                $table->string('left_photo_path')->nullable();
                $table->string('right_photo_path')->nullable();
                $table->string('interior_photo_path')->nullable();
                $table->text('description')->nullable();
                $table->string('status')->default('pending');
                $table->boolean('is_verified')->default(false);
                $table->foreignId('verified_by')->nullable()->constrained('users');
                $table->timestamp('verified_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
                
                $table->index('driver_id');
                $table->index('vehicle_number');
                $table->index('registration_number');
                $table->index('status');
            });
        } else {
            // Table exists, just add any missing columns
            Schema::table('vehicles', function (Blueprint $table) {
                if (!Schema::hasColumn('vehicles', 'driver_code')) {
                    $table->string('driver_code')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'custom_vehicle_type')) {
                    $table->string('custom_vehicle_type')->nullable();
                }
                // Add other missing columns as needed
            });
        }
    }

    public function down(): void
    {
        // Do not drop the table in production
        // Schema::dropIfExists('vehicles');
    }
};