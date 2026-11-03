<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'preferred_latitude')) {
                $table->decimal('preferred_latitude', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('users', 'preferred_longitude')) {
                $table->decimal('preferred_longitude', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('users', 'preferred_location_name')) {
                $table->string('preferred_location_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'search_radius')) {
                $table->integer('search_radius')->default(10);
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_latitude', 'preferred_longitude', 
                'preferred_location_name', 'search_radius'
            ]);
        });
    }
};