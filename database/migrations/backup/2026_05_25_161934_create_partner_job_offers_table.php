<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('partner_job_offers', function (Blueprint $table) {
            $table->id();
            $table->morphs('job');           // job_type + job_id (polymorphic)
            $table->foreignId('partner_id')->constrained('users')->onDelete('cascade');
            $table->decimal('proposed_price', 10, 2);
            $table->decimal('admin_final_price', 10, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_job_offers');
    }
};