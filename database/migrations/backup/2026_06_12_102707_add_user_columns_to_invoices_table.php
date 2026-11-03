<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('invoices')) {
            if (!Schema::hasColumn('invoices', 'user_id')) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->foreignId('user_id')->nullable()->after('id');
                });
            }
            
            if (!Schema::hasColumn('invoices', 'client_id')) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->foreignId('client_id')->nullable()->after('user_id');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn(['user_id', 'client_id']);
            });
        }
    }
};