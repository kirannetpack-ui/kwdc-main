<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_agencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('agency_name');
            $table->string('registration_number')->nullable();
            $table->string('license_number')->nullable();
            $table->text('address');
            $table->string('phone')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('email')->nullable();
            $table->text('services_offered')->nullable();
            $table->string('year_established')->nullable();
            $table->string('pan_vat_number')->nullable();
            $table->text('certifications')->nullable();
            $table->string('logo')->nullable();
            $table->enum('status', ['pending', 'approved', 'suspended'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_agencies');
    }
};