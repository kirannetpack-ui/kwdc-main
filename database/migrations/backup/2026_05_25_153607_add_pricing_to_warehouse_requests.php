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
        $table->decimal('agreed_price_per_unit', 10, 2)->nullable();
        $table->decimal('security_deposit', 10, 2)->nullable();
        $table->decimal('monthly_rent', 10, 2)->nullable();
        $table->date('contract_end_date')->nullable();
        $table->date('last_invoice_date')->nullable();
        $table->boolean('goods_auctioned')->default(false);
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
