<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouses', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'price_unit')) {
                $table->string('price_unit')->default('sqft');
            }
        });
    }

    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['description', 'price_unit']);
        });
    }
};