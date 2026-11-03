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
    Schema::create('insurances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('warehouse_request_id')->constrained()->onDelete('cascade');
        $table->string('provider')->nullable();
        $table->string('policy_number')->nullable();
        $table->decimal('premium', 10, 2)->nullable();
        $table->date('start_date');
        $table->date('end_date');
        $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};
