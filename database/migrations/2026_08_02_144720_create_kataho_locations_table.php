<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('kataho_locations')) {
            Schema::create('kataho_locations', function (Blueprint $table) {
                $table->id();
                $table->string('reference_type')->nullable(); // user, warehouse, dispatch, pickup
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('location_type')->nullable(); // pickup, delivery, warehouse
                $table->string('kataho_code')->nullable();
                $table->string('grid_id')->nullable();
                $table->string('address')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->text('full_address')->nullable();
                $table->string('ward_number')->nullable();
                $table->string('municipality')->nullable();
                $table->string('district')->nullable();
                $table->string('province')->nullable();
                $table->string('country')->default('Nepal');
                $table->json('metadata')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();

                $table->index(['reference_type', 'reference_id']);
                $table->index('kataho_code');
                $table->index('grid_id');
                $table->index('location_type');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('kataho_locations');
    }
};