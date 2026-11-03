<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('driver_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->decimal('flat_rate_0_5', 10, 2)->default(200);
            $table->decimal('flat_rate_6_10', 10, 2)->default(350);
            $table->decimal('flat_rate_11_20', 10, 2)->default(600);
            $table->decimal('rate_per_km_21_plus', 10, 2)->default(40);
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver_rates');
    }
};