<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouse_requests', 'assigned_warehouse_id')) {
                $table->foreignId('assigned_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            }

            if (!Schema::hasColumn('warehouse_requests', 'agreed_price_per_unit')) {
                $table->decimal('agreed_price_per_unit', 12, 2)->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'monthly_rent')) {
                $table->decimal('monthly_rent', 12, 2)->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'last_invoice_date')) {
                $table->date('last_invoice_date')->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'goods_auctioned')) {
                $table->boolean('goods_auctioned')->default(false);
            }

            if (!Schema::hasColumn('warehouse_requests', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'cancellation_notes')) {
                $table->text('cancellation_notes')->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'contract_end_date')) {
                $table->date('contract_end_date')->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'contract_signed_at')) {
                $table->timestamp('contract_signed_at')->nullable();
            }

            if (!Schema::hasColumn('warehouse_requests', 'contract_expires_at')) {
                $table->date('contract_expires_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // This migration aligns production schemas that may already contain
        // these columns from earlier deployments, so rollback intentionally
        // preserves data instead of guessing which columns are safe to remove.
    }
};
