<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouses', 'price_per_unit')) {
                $table->decimal('price_per_unit', 10, 2)->nullable()->after('distance_from_city');
            }
            if (!Schema::hasColumn('warehouses', 'price_unit_type')) {
                $table->string('price_unit_type')->default('fixed')->after('price_per_unit'); // 'fixed' or 'percentage'
            }
            if (!Schema::hasColumn('warehouses', 'security_deposit_percentage')) {
                $table->decimal('security_deposit_percentage', 5, 2)->nullable()->after('price_unit_type');
            }
            if (!Schema::hasColumn('warehouses', 'security_deposit_fixed')) {
                $table->decimal('security_deposit_fixed', 10, 2)->nullable()->after('security_deposit_percentage');
            }
        });
    }

    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['price_per_unit', 'price_unit_type', 'security_deposit_percentage', 'security_deposit_fixed']);
        });
    }
};