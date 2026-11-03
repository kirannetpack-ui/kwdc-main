<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pickup_stops')) {
            Schema::create('pickup_stops', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pickup_request_id')->constrained()->onDelete('cascade');
                $table->integer('stop_number');
                $table->text('address');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('contact_name');
                $table->string('contact_phone');
                $table->text('items_description')->nullable();
                $table->decimal('estimated_weight', 10, 2)->default(0);
                $table->decimal('distance_price', 10, 2)->default(0);
                $table->string('status')->default('pending');
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamps();
                
                $table->index('pickup_request_id');
                $table->index('status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pickup_stops');
    }
};