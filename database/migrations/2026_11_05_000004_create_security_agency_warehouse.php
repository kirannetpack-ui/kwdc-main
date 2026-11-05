<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_agency_warehouse', function (Blueprint $table) {
            $table->foreignId('security_agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->primary(['security_agency_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_agency_warehouse');
    }
};
