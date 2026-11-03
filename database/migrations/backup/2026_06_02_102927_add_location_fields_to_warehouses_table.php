<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouses', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'security_features')) {
                $table->text('security_features')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'nearest_police')) {
                $table->string('nearest_police')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'nearest_fire_station')) {
                $table->string('nearest_fire_station')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'nearest_hospital')) {
                $table->string('nearest_hospital')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn([
                'latitude', 'longitude', 'security_features',
                'nearest_police', 'nearest_fire_station', 'nearest_hospital'
            ]);
        });
    }
};