<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only run if table exists
        if (Schema::hasTable('vehicles')) {
            
            // List of columns to add if missing
            $columns = [
                'driver_code' => 'string',
                'custom_vehicle_type' => 'string',
                'manufacturer' => 'string',
                'model' => 'string',
                'year' => 'integer',
                'color' => 'string',
                'fuel_type' => 'string',
                'registration_date' => 'date',
                'insurance_number' => 'string',
                'insurance_valid_until' => 'date',
                'insurance_file_path' => 'string',
                'fitness_certificate_number' => 'string',
                'fitness_valid_until' => 'date',
                'fitness_file_path' => 'string',
                'pollution_certificate_number' => 'string',
                'pollution_valid_until' => 'date',
                'pollution_file_path' => 'string',
                'permit_number' => 'string',
                'permit_valid_until' => 'date',
                'permit_file_path' => 'string',
                'blue_book_number' => 'string',
                'blue_book_file_path' => 'string',
                'front_photo_path' => 'string',
                'back_photo_path' => 'string',
                'left_photo_path' => 'string',
                'right_photo_path' => 'string',
                'interior_photo_path' => 'string',
                'verified_by' => 'foreignId',
                'verified_at' => 'timestamp',
                'rejection_reason' => 'text',
            ];
            
            Schema::table('vehicles', function (Blueprint $table) use ($columns) {
                // Add driver_code
                if (!Schema::hasColumn('vehicles', 'driver_code')) {
                    $table->string('driver_code')->nullable();
                }
                
                // Add custom_vehicle_type
                if (!Schema::hasColumn('vehicles', 'custom_vehicle_type')) {
                    $table->string('custom_vehicle_type')->nullable();
                }
                
                // Add manufacturer
                if (!Schema::hasColumn('vehicles', 'manufacturer')) {
                    $table->string('manufacturer')->nullable();
                }
                
                // Add model
                if (!Schema::hasColumn('vehicles', 'model')) {
                    $table->string('model')->nullable();
                }
                
                // Add year
                if (!Schema::hasColumn('vehicles', 'year')) {
                    $table->integer('year')->nullable();
                }
                
                // Add color
                if (!Schema::hasColumn('vehicles', 'color')) {
                    $table->string('color')->nullable();
                }
                
                // Add fuel_type
                if (!Schema::hasColumn('vehicles', 'fuel_type')) {
                    $table->string('fuel_type')->nullable();
                }
                
                // Add registration_date
                if (!Schema::hasColumn('vehicles', 'registration_date')) {
                    $table->date('registration_date')->nullable();
                }
                
                // Add insurance fields
                if (!Schema::hasColumn('vehicles', 'insurance_number')) {
                    $table->string('insurance_number')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'insurance_valid_until')) {
                    $table->date('insurance_valid_until')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'insurance_file_path')) {
                    $table->string('insurance_file_path')->nullable();
                }
                
                // Add fitness fields
                if (!Schema::hasColumn('vehicles', 'fitness_certificate_number')) {
                    $table->string('fitness_certificate_number')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'fitness_valid_until')) {
                    $table->date('fitness_valid_until')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'fitness_file_path')) {
                    $table->string('fitness_file_path')->nullable();
                }
                
                // Add pollution fields
                if (!Schema::hasColumn('vehicles', 'pollution_certificate_number')) {
                    $table->string('pollution_certificate_number')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'pollution_valid_until')) {
                    $table->date('pollution_valid_until')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'pollution_file_path')) {
                    $table->string('pollution_file_path')->nullable();
                }
                
                // Add permit fields
                if (!Schema::hasColumn('vehicles', 'permit_number')) {
                    $table->string('permit_number')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'permit_valid_until')) {
                    $table->date('permit_valid_until')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'permit_file_path')) {
                    $table->string('permit_file_path')->nullable();
                }
                
                // Add blue book fields
                if (!Schema::hasColumn('vehicles', 'blue_book_number')) {
                    $table->string('blue_book_number')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'blue_book_file_path')) {
                    $table->string('blue_book_file_path')->nullable();
                }
                
                // Add photo fields
                if (!Schema::hasColumn('vehicles', 'front_photo_path')) {
                    $table->string('front_photo_path')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'back_photo_path')) {
                    $table->string('back_photo_path')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'left_photo_path')) {
                    $table->string('left_photo_path')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'right_photo_path')) {
                    $table->string('right_photo_path')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'interior_photo_path')) {
                    $table->string('interior_photo_path')->nullable();
                }
                
                // Add verification fields
                if (!Schema::hasColumn('vehicles', 'verified_by')) {
                    $table->foreignId('verified_by')->nullable()->constrained('users');
                }
                if (!Schema::hasColumn('vehicles', 'verified_at')) {
                    $table->timestamp('verified_at')->nullable();
                }
                if (!Schema::hasColumn('vehicles', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Revert changes if needed
    }
};
