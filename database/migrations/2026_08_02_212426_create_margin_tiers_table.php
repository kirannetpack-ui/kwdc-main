<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('margin_tiers')) {
            Schema::create('margin_tiers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('min_distance', 10, 2)->default(0);
                $table->decimal('max_distance', 10, 2)->nullable();
                $table->decimal('margin_percentage', 5, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('is_active');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('margin_tiers');
    }
};