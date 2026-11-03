<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            // Make dimension columns nullable
            $table->float('length')->nullable()->change();
            $table->float('width')->nullable()->change();
            $table->float('height')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->float('length')->nullable(false)->change();
            $table->float('width')->nullable(false)->change();
            $table->float('height')->nullable(false)->change();
        });
    }
};