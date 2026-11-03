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
    Schema::create('warehouse_documents', function (Blueprint $table) {
        $table->id();
        $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
        $table->string('title'); // e.g. "Lalpurja", "Tax Clearance"
        $table->string('file_path');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_documents');
    }
};
