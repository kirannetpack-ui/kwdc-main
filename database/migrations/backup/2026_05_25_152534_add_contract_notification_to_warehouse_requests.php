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
    Schema::table('warehouse_requests', function (Blueprint $table) {
        $table->string('last_notification_sent')->nullable(); // '1month', '15days', '5days', 'extended'
        $table->date('extended_until')->nullable();
        $table->date('goods_auctioned_at')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_requests', function (Blueprint $table) {
            //
        });
    }
};
