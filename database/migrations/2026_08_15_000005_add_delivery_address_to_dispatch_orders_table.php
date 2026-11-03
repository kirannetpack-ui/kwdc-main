<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('dispatch_orders', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('pickup_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            if (Schema::hasColumn('dispatch_orders', 'delivery_address')) {
                $table->dropColumn('delivery_address');
            }
        });
    }
};
