<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('dispatch_orders', 'base_price')) {
                $table->decimal('base_price', 12, 2)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            if (Schema::hasColumn('dispatch_orders', 'base_price')) {
                $table->dropColumn('base_price');
            }
        });
    }
};