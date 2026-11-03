<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('dispatch_orders', 'bill_document')) {
                $table->string('bill_document')->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'packing_list')) {
                $table->string('packing_list')->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'delivery_confirmation')) {
                $table->string('delivery_confirmation')->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'other_documents')) {
                $table->json('other_documents')->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'invoice_number')) {
                $table->string('invoice_number')->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'bill_type')) {
                $table->string('bill_type')->default('regular');
            }
            if (!Schema::hasColumn('dispatch_orders', 'pan_number')) {
                $table->string('pan_number')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->dropColumn([
                'bill_document', 'packing_list', 'delivery_confirmation', 
                'other_documents', 'invoice_number', 'bill_type', 'pan_number'
            ]);
        });
    }
};