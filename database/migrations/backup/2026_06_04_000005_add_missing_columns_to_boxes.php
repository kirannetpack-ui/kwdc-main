<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('boxes', function (Blueprint $table) {
            // Add invoice_document if missing
            if (!Schema::hasColumn('boxes', 'invoice_document')) {
                $table->string('invoice_document')->nullable();
            }
            // Add packing_list_document if missing
            if (!Schema::hasColumn('boxes', 'packing_list_document')) {
                $table->string('packing_list_document')->nullable();
            }
            // Add insurance_document if missing
            if (!Schema::hasColumn('boxes', 'insurance_document')) {
                $table->string('insurance_document')->nullable();
            }
            // Add other_documents if missing
            if (!Schema::hasColumn('boxes', 'other_documents')) {
                $table->json('other_documents')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('boxes', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_document',
                'packing_list_document',
                'insurance_document',
                'other_documents'
            ]);
        });
    }
};