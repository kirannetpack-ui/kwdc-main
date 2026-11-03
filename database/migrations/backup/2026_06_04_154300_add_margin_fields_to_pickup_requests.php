<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('pickup_requests', 'admin_margin')) {
                $table->decimal('admin_margin', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('pickup_requests', 'driver_earning')) {
                $table->decimal('driver_earning', 10, 2)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropColumn(['admin_margin', 'driver_earning']);
        });
    }
};