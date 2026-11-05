<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_request_id')->nullable()->change();
        });
    }
};
