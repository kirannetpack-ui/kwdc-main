<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, check what columns exist in dispatch_orders
        if (Schema::hasTable('dispatch_orders')) {
            $existingColumns = Schema::getColumnListing('dispatch_orders');
            
            // Add base columns first if they don't exist
            if (!in_array('total_price', $existingColumns)) {
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->decimal('total_price', 10, 2)->default(0);
                });
            }
            
            if (!in_array('base_price', $existingColumns)) {
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->decimal('base_price', 10, 2)->default(0);
                });
            }
            
            // Now add financial columns safely (without 'after' clause)
            if (!in_array('amount', $existingColumns)) {
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->decimal('amount', 10, 2)->default(0);
                });
            }
            
            if (!in_array('paid_amount', $existingColumns)) {
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->decimal('paid_amount', 10, 2)->default(0);
                });
            }
            
            if (!in_array('due_amount', $existingColumns)) {
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->decimal('due_amount', 10, 2)->default(0);
                });
            }
            
            if (!in_array('total_distance', $existingColumns)) {
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->decimal('total_distance', 10, 2)->default(0);
                });
            }
            
            if (!in_array('distance_km', $existingColumns)) {
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->decimal('distance_km', 10, 2)->default(0);
                });
            }
            
            if (!in_array('payment_date', $existingColumns)) {
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->date('payment_date')->nullable();
                });
            }
        }
        
        // Fix pickup_requests table
        if (Schema::hasTable('pickup_requests')) {
            $existingColumns = Schema::getColumnListing('pickup_requests');
            
            if (!in_array('total_price', $existingColumns)) {
                Schema::table('pickup_requests', function (Blueprint $table) {
                    $table->decimal('total_price', 10, 2)->default(0);
                });
            }
            
            if (!in_array('amount', $existingColumns)) {
                Schema::table('pickup_requests', function (Blueprint $table) {
                    $table->decimal('amount', 10, 2)->default(0);
                });
            }
            
            if (!in_array('paid_amount', $existingColumns)) {
                Schema::table('pickup_requests', function (Blueprint $table) {
                    $table->decimal('paid_amount', 10, 2)->default(0);
                });
            }
            
            if (!in_array('due_amount', $existingColumns)) {
                Schema::table('pickup_requests', function (Blueprint $table) {
                    $table->decimal('due_amount', 10, 2)->default(0);
                });
            }
        }
        
        // Fix equipment_jobs table
        if (Schema::hasTable('equipment_jobs')) {
            $existingColumns = Schema::getColumnListing('equipment_jobs');
            
            if (!in_array('price', $existingColumns)) {
                Schema::table('equipment_jobs', function (Blueprint $table) {
                    $table->decimal('price', 10, 2)->default(0);
                });
            }
            
            if (!in_array('amount', $existingColumns)) {
                Schema::table('equipment_jobs', function (Blueprint $table) {
                    $table->decimal('amount', 10, 2)->default(0);
                });
            }
            
            if (!in_array('paid_amount', $existingColumns)) {
                Schema::table('equipment_jobs', function (Blueprint $table) {
                    $table->decimal('paid_amount', 10, 2)->default(0);
                });
            }
        }
    }

    public function down()
    {
        // Remove columns if they exist
        $tables = ['dispatch_orders', 'pickup_requests', 'equipment_jobs'];
        $columns = ['amount', 'paid_amount', 'due_amount', 'payment_date'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    foreach ($columns as $column) {
                        if (Schema::hasColumn($this->getTableName(), $column)) {
                            $table->dropColumn($column);
                        }
                    }
                });
            }
        }
    }
    
    private function getTableName()
    {
        // This will be set dynamically, but we'll handle it differently
        return '';
    }
};