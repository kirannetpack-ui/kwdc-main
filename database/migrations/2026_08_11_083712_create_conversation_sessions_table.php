<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('conversation_sessions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('intent')->nullable();
        $table->json('context')->nullable();
        $table->json('collected_data')->nullable();
        $table->string('status')->default('idle');
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('conversation_sessions');
}
};
