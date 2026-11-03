<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouse_requests', 'required_area')) {
                $table->decimal('required_area', 12, 2)->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'duration_months')) {
                $table->integer('duration_months')->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'preferred_start_date')) {
                $table->date('preferred_start_date')->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'contact_person')) {
                $table->string('contact_person')->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'contact_phone')) {
                $table->string('contact_phone')->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'invoice_path')) {
                $table->string('invoice_path')->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'packing_list_path')) {
                $table->string('packing_list_path')->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'insurance_path')) {
                $table->string('insurance_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        $columnsToDrop = array_filter([
            'required_area',
            'duration_months',
            'preferred_start_date',
            'contact_person',
            'contact_phone',
            'invoice_path',
            'packing_list_path',
            'insurance_path',
        ], fn (string $column): bool => Schema::hasColumn('warehouse_requests', $column));

        if ($columnsToDrop === []) {
            return;
        }

        Schema::table('warehouse_requests', function (Blueprint $table) use ($columnsToDrop) {
            $table->dropColumn($columnsToDrop);
        });
    }
};
