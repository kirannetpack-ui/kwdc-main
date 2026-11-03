<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('equipment')) {
            Schema::create('equipment', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users');
                $table->foreignId('owner_id')->nullable()->constrained('users');
                $table->string('name');
                $table->string('type');
                $table->string('model')->nullable();
                $table->integer('year')->nullable();
                $table->text('description')->nullable();
                
                // Specifications
                $table->decimal('weight', 10, 2)->nullable();
                $table->decimal('engine_power', 10, 2)->nullable();
                $table->decimal('bucket_capacity', 10, 2)->nullable();
                $table->decimal('max_reach', 10, 2)->nullable();
                
                // Pricing
                $table->decimal('daily_rate', 10, 2)->nullable();
                $table->decimal('weekly_rate', 10, 2)->nullable();
                $table->decimal('monthly_rate', 10, 2)->nullable();
                $table->decimal('security_deposit', 10, 2)->nullable();
                
                // Location
                $table->string('location')->nullable();
                $table->string('status')->default('available');
                
                // Photos
                $table->string('front_photo')->nullable();
                $table->string('side_photo')->nullable();
                $table->string('working_photo')->nullable();
                
                // Documents
                $table->string('registration_doc')->nullable();
                $table->string('insurance_doc')->nullable();
                
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('equipment');
    }
};