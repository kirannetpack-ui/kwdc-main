<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
        });
    }

    public function down()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->dropColumn(['contact_person', 'contact_phone']);
        });
    }
};