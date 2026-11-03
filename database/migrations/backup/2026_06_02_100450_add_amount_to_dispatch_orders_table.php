<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('dispatch_orders', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'delivery_address')) {
                $table->text('delivery_address')->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'pickup_address')) {
                $table->text('pickup_address')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->dropColumn(['amount', 'delivery_address', 'pickup_address']);
        });
    }
};