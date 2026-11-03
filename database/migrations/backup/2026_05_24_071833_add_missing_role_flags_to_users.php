<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'is_client')) $table->boolean('is_client')->default(false);
        if (!Schema::hasColumn('users', 'is_property_owner')) $table->boolean('is_property_owner')->default(false);
        if (!Schema::hasColumn('users', 'is_driver')) $table->boolean('is_driver')->default(false);
        if (!Schema::hasColumn('users', 'is_equipment_owner')) $table->boolean('is_equipment_owner')->default(false);
        if (!Schema::hasColumn('users', 'is_admin')) $table->boolean('is_admin')->default(false);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
