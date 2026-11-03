<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            // Convert ENUM to string (works on PostgreSQL)
            $table->string('status', 50)->default('pending')->change();
        });
    }

    public function down()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
        });
    }
};