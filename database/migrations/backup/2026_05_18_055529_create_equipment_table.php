<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // crane, forklift, etc.
            $table->string('model')->nullable();
            $table->decimal('capacity_kg', 10, 2)->nullable();
            $table->decimal('base_charge', 10, 2)->nullable();
            $table->boolean('is_negotiable')->default(true);
            $table->boolean('is_available')->default(true);
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipment');
    }
};