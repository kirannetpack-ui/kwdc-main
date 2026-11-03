<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('equipment_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipment_id')->nullable()->constrained();
            $table->enum('status', ['pending', 'accepted', 'completed', 'cancelled'])->default('pending');
            $table->decimal('agreed_rate', 10, 2)->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipment_jobs');
    }
};