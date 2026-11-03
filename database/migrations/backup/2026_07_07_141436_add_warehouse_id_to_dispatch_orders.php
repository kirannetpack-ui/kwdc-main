<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            // Check if column exists before adding
            if (!Schema::hasColumn('dispatch_orders', 'warehouse_id')) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('driver_id');
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }
};