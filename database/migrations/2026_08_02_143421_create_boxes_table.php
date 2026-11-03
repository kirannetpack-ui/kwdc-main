<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('boxes')) {
            Schema::create('boxes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_id')->constrained()->onDelete('cascade');
                $table->string('box_number');
                $table->string('qr_code')->unique();
                $table->text('qr_code_data');
                $table->string('status')->default('active');
                $table->foreignId('dispatch_order_id')->nullable()->constrained();
                $table->foreignId('client_id')->nullable()->constrained('users');
                $table->text('description')->nullable();
                $table->json('documents')->nullable();
                $table->timestamps();
                
                $table->index('qr_code');
                $table->index('status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('boxes');
    }
};