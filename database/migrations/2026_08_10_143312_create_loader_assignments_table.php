<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loader_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')->constrained('dispatch_orders')->onDelete('cascade');
            $table->foreignId('loader_manager_id')->constrained('loader_managers')->onDelete('cascade');
            $table->json('loader_ids')->nullable();
            $table->integer('required_loaders')->default(1);
            $table->date('assignment_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loader_assignments');
    }
};