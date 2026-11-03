<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('delivery_stops', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_stops', 'invoice_document')) {
                $table->string('invoice_document')->nullable();
            }
            if (!Schema::hasColumn('delivery_stops', 'invoice_number')) {
                $table->string('invoice_number')->nullable();
            }
            if (!Schema::hasColumn('delivery_stops', 'received_by')) {
                $table->string('received_by')->nullable();
            }
            if (!Schema::hasColumn('delivery_stops', 'received_at')) {
                $table->timestamp('received_at')->nullable();
            }
            if (!Schema::hasColumn('delivery_stops', 'signature')) {
                $table->string('signature')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('delivery_stops', function (Blueprint $table) {
            $table->dropColumn(['invoice_document', 'invoice_number', 'received_by', 'received_at', 'signature']);
        });
    }
};