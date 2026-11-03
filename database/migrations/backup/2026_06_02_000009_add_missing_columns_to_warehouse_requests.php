<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouse_requests', function (Blueprint $table) {
            // Add warehouse_id if missing
            if (!Schema::hasColumn('warehouse_requests', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            }
            
            // Add other missing columns if needed
            if (!Schema::hasColumn('warehouse_requests', 'assigned_warehouse_id')) {
                $table->foreignId('assigned_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('warehouse_requests', 'required_area')) {
                $table->decimal('required_area', 12, 2)->nullable();
            }
            
            if (!Schema::hasColumn('warehouse_requests', 'duration_months')) {
                $table->integer('duration_months')->nullable();
            }
            
            if (!Schema::hasColumn('warehouse_requests', 'purpose')) {
                $table->text('purpose')->nullable();
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
            
            if (!Schema::hasColumn('warehouse_requests', 'agreed_price')) {
                $table->decimal('agreed_price', 12, 2)->nullable();
            }
            
            if (!Schema::hasColumn('warehouse_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
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
        });
    }

    public function down()
    {
        Schema::table('warehouse_requests', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['assigned_warehouse_id']);
            $table->dropColumn([
                'warehouse_id', 'assigned_warehouse_id', 'required_area', 'duration_months',
                'purpose', 'preferred_start_date', 'contact_person', 'contact_phone',
                'agreed_price', 'approved_at', 'assigned_at', 'completed_at', 
                'cancelled_at', 'cancellation_notes'
            ]);
        });
    }
};