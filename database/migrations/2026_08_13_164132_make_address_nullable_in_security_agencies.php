<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('security_agencies', function (Blueprint $table) {
            $table->string('address')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('security_agencies', function (Blueprint $table) {
            $table->string('address')->nullable(false)->change();
        });
    }
};