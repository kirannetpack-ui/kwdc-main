<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('stocks')) {
            if (!Schema::hasColumn('stocks', 'user_id')) {
                Schema::table('stocks', function (Blueprint $table) {
                    $table->foreignId('user_id')->nullable()->after('id');
                });
            }
            
            // If client_id exists, copy values to user_id
            if (Schema::hasColumn('stocks', 'client_id')) {
                DB::statement('UPDATE stocks SET user_id = client_id WHERE user_id IS NULL');
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('stocks')) {
            if (Schema::hasColumn('stocks', 'user_id')) {
                Schema::table('stocks', function (Blueprint $table) {
                    $table->dropColumn('user_id');
                });
            }
        }
    }
};