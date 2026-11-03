<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_client')) {
                $table->boolean('is_client')->default(false)->after('is_admin');
            }

            if (!Schema::hasColumn('users', 'is_property_owner')) {
                $table->boolean('is_property_owner')->default(false)->after('is_driver');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_property_owner')) {
                $table->dropColumn('is_property_owner');
            }

            if (Schema::hasColumn('users', 'is_client')) {
                $table->dropColumn('is_client');
            }
        });
    }
};
