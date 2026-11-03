<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loader_job_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loader_id')->constrained('loaders')->onDelete('cascade');
            $table->foreignId('assignment_id')->constrained('loader_assignments')->onDelete('cascade');
            $table->timestamp('check_in_time');
            $table->timestamp('check_out_time')->nullable();
            $table->decimal('hours_worked', 5, 2)->nullable();
            $table->decimal('amount_earned', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loader_job_logs');
    }
};