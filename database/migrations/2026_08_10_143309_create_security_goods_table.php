<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_goods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('security_agencies')->onDelete('cascade');
            $table->string('item_name');
            $table->string('category');
            $table->string('model')->nullable();
            $table->text('specifications')->nullable();
            $table->integer('quantity_available')->default(0);
            $table->decimal('unit_price', 12, 2);
            $table->boolean('is_rental')->default(false);
            $table->decimal('rental_rate_per_day', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['available', 'rented', 'maintenance'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_goods');
    }
};