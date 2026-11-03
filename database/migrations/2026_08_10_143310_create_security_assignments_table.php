<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('agency_id')->constrained('security_agencies')->onDelete('cascade');
            $table->foreignId('personnel_id')->nullable()->constrained('security_personnel')->onDelete('set null');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('shift', ['day', 'night', '24_hours', 'custom'])->default('day');
            $table->time('shift_start')->nullable();
            $table->time('shift_end')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_assignments');
    }
};