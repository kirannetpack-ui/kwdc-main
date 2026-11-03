<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('boxes', function (Blueprint $table) {
            if (!Schema::hasColumn('boxes', 'client_id')) {
                $table->foreignId('client_id')->nullable()->constrained('users')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('boxes', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};