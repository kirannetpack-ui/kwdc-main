<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->decimal('total_capacity', 10, 2)->nullable()->after('height');
            $table->decimal('allocated_space', 10, 2)->default(0)->after('total_capacity');
            $table->boolean('allow_shared')->default(true)->after('allocated_space');
        });
    }

    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['total_capacity', 'allocated_space', 'allow_shared']);
        });
    }
};