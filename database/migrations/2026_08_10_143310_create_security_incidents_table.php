<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('reported_by_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assignment_id')->nullable()->constrained('security_assignments')->onDelete('set null');
            $table->timestamp('incident_time');
            $table->string('category');
            $table->text('description');
            $table->string('severity')->default('medium');
            $table->text('actions_taken')->nullable();
            $table->json('attachments')->nullable();
            $table->enum('status', ['reported', 'investigating', 'resolved'])->default('reported');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_incidents');
    }
};