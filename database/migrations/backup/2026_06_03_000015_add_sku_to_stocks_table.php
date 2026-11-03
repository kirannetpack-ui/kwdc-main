<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('stocks', 'sku')) {
                $table->string('sku')->nullable();
            }
            if (!Schema::hasColumn('stocks', 'unit')) {
                $table->string('unit')->default('pcs');
            }
            if (!Schema::hasColumn('stocks', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['sku', 'unit', 'warehouse_id']);
        });
    }
};