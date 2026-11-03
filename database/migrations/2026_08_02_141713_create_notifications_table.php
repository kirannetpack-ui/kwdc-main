<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('type');
                $table->string('title');
                $table->text('message');
                $table->unsignedBigInteger('related_id')->nullable();
                $table->string('related_type')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('is_read');
                $table->index('created_at');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};