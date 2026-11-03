<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('partner_proposals', function (Blueprint $table) {
            $table->id();
            $table->morphs('job');
            $table->foreignId('partner_id')->constrained('users')->onDelete('cascade');
            $table->decimal('proposed_price', 10, 2);
            $table->decimal('admin_margin', 10, 2);
            $table->enum('status', ['pending', 'accepted', 'rejected', 'negotiating'])->default('pending');
            $table->text('negotiation_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_proposals');
    }
};