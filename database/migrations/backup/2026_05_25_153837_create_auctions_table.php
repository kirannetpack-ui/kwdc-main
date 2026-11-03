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
    Schema::create('auctions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('warehouse_request_id')->constrained()->onDelete('cascade');
        $table->date('scheduled_date');
        $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
