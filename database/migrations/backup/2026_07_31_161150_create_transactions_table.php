<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if table exists, if not create it
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained();
                $table->foreignId('user_id')->constrained();
                $table->decimal('amount', 10, 2);
                $table->string('payment_method'); // khalti, esewa, bank_transfer, cash
                $table->string('transaction_id')->unique();
                $table->string('status')->default('pending'); // pending, completed, failed
                $table->json('payment_details')->nullable();
                $table->timestamps();
                
                $table->index('transaction_id');
                $table->index('status');
            });
        } else {
            // Table exists, add missing columns if needed
            Schema::table('transactions', function (Blueprint $table) {
                $columns = Schema::getColumnListing('transactions');
                
                // Add receipt_no column if missing
                if (!in_array('receipt_no', $columns)) {
                    $table->string('receipt_no')->unique()->nullable()->after('transaction_id');
                }
                
                // Add tax column if missing
                if (!in_array('tax', $columns)) {
                    $table->decimal('tax', 10, 2)->default(0)->after('amount');
                }
                
                // Add payment_date column if missing
                if (!in_array('payment_date', $columns)) {
                    $table->timestamp('payment_date')->nullable()->after('status');
                }
                
                // Add notes column if missing
                if (!in_array('notes', $columns)) {
                    $table->text('notes')->nullable()->after('payment_details');
                }
                
                // Add transactionable columns for polymorphic relationship
                if (!in_array('transactionable_type', $columns)) {
                    $table->string('transactionable_type')->nullable()->after('id');
                }
                
                if (!in_array('transactionable_id', $columns)) {
                    $table->unsignedBigInteger('transactionable_id')->nullable()->after('transactionable_type');
                }
            });
        }
    }

    public function down()
    {
        // Only drop if we created it
        if (Schema::hasTable('transactions')) {
            // Check if we added any columns and remove them
            $columns = Schema::getColumnListing('transactions');
            $addedColumns = ['receipt_no', 'tax', 'payment_date', 'notes', 'transactionable_type', 'transactionable_id'];
            
            foreach ($addedColumns as $column) {
                if (in_array($column, $columns)) {
                    Schema::table('transactions', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};