<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('dispatch_orders', function (Blueprint $table) {
        $table->boolean('partner_paid')->default(false);
    });
    Schema::table('pickup_requests', function (Blueprint $table) {
        $table->boolean('partner_paid')->default(false);
    });
    Schema::table('equipment_jobs', function (Blueprint $table) {
        $table->boolean('partner_paid')->default(false);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_tables', function (Blueprint $table) {
            //
        });
    }
};
