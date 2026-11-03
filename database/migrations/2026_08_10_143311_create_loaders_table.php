<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loaders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('address')->nullable();
            $table->string('citizenship_number')->nullable();
            $table->date('dob')->nullable();
            $table->string('photo')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->boolean('is_available')->default(true);
            $table->decimal('rate_per_hour', 10, 2)->default(0);
            $table->json('skills')->nullable();
            $table->integer('experience_years')->default(0);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loaders');
    }
};