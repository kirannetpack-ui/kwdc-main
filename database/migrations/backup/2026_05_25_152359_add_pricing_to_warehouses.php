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
    Schema::table('warehouses', function (Blueprint $table) {
        $table->decimal('price_per_unit', 10, 2)->nullable(); // per m³ or m²
        $table->decimal('security_deposit_percent', 5, 2)->nullable(); // % of total contract value
        $table->decimal('security_deposit_fixed', 10, 2)->nullable(); // fixed amount
        $table->enum('price_unit', ['month', 'quarter', 'year'])->default('month');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            //
        });
    }
};
