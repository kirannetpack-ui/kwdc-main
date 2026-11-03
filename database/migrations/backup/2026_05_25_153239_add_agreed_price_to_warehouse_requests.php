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
        $table->decimal('agreed_price', 10, 2)->nullable();
        $table->decimal('security_deposit_paid', 10, 2)->nullable();
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
