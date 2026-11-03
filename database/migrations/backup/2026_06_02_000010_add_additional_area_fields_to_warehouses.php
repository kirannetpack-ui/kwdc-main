<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouses', 'built_up_area_sqm')) {
                $table->decimal('built_up_area_sqm', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'open_area_sqm')) {
                $table->decimal('open_area_sqm', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'usable_area_sqm')) {
                $table->decimal('usable_area_sqm', 12, 2)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['built_up_area_sqm', 'open_area_sqm', 'usable_area_sqm']);
        });
    }
};