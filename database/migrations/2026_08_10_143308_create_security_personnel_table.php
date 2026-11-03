<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_personnel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('security_agencies')->onDelete('cascade');
            $table->string('name');
            $table->string('photo')->nullable();
            $table->string('employee_id')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('citizenship_number')->nullable();
            $table->date('dob')->nullable();
            $table->string('address')->nullable();
            $table->string('position')->nullable();
            $table->text('qualifications')->nullable();
            $table->string('training_certificate')->nullable();
            $table->date('training_expiry')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->boolean('has_vehicle')->default(false);
            $table->string('vehicle_type')->nullable();
            $table->json('shift_availability')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_personnel');
    }
};