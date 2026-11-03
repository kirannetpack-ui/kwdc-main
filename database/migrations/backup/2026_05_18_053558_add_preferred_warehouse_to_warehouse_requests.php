<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouse_requests', function (Blueprint $table) {
            $table->foreignId('preferred_warehouse_id')->nullable()->constrained('warehouses');
        });
    }

    public function down()
    {
        Schema::table('warehouse_requests', function (Blueprint $table) {
            $table->dropForeign(['preferred_warehouse_id']);
            $table->dropColumn('preferred_warehouse_id');
        });
    }
};