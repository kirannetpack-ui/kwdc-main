<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'driver_id')) {
                $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('vehicles', 'owner_id')) {
                $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropColumn('driver_id');
            $table->dropForeign(['owner_id']);
            $table->dropColumn('owner_id');
        });
    }
};