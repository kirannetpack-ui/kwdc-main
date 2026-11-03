<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('notes')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('remind_at')->nullable();
            $table->timestamp('emailed_at')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamps();

            $table->index(['user_id', 'starts_at']);
            $table->index(['status', 'remind_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reminders');
    }
};
