<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_order_id')->constrained()->onDelete('cascade');
            $table->integer('stop_order');
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('boxes_count')->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_stops');
    }
};