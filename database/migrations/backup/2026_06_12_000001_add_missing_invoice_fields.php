<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('invoices')) {
            
            // Check and add missing columns one by one
            if (!Schema::hasColumn('invoices', 'qr_code')) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->string('qr_code')->nullable();
                });
            }
            
            if (!Schema::hasColumn('invoices', 'billing_type')) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->string('billing_type')->default('regular');
                });
            }
            
            if (!Schema::hasColumn('invoices', 'pan_number')) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->string('pan_number')->nullable();
                });
            }
            
            if (!Schema::hasColumn('invoices', 'billing_address')) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->text('billing_address')->nullable();
                });
            }
            
            // Check for items column - if not exists, add it
            if (!Schema::hasColumn('invoices', 'items')) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->json('items')->nullable();
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('invoices')) {
            $columns = ['qr_code', 'billing_type', 'pan_number', 'billing_address', 'items'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    Schema::table('invoices', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};