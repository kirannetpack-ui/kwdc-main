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
    Schema::table('warehouse_requests', function (Blueprint $table) {
        if (!Schema::hasColumn('warehouse_requests', 'assigned_warehouse_id')) {
            $table->foreignId('assigned_warehouse_id')->nullable()->after('preferred_warehouse_id')->constrained('warehouses');
        }
    });
}    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_requests', function (Blueprint $table) {
            //
        });
    }
};
