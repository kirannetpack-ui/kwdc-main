<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add columns to dispatch_orders without using 'after' clause
        if (Schema::hasTable('dispatch_orders')) {
            Schema::table('dispatch_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('dispatch_orders', 'tracking_id')) {
                    $table->string('tracking_id')->unique()->nullable();
                }
                if (!Schema::hasColumn('dispatch_orders', 'invoice_no')) {
                    $table->string('invoice_no')->unique()->nullable();
                }
                if (!Schema::hasColumn('dispatch_orders', 'tax_amount')) {
                    $table->decimal('tax_amount', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('dispatch_orders', 'grand_total')) {
                    $table->decimal('grand_total', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('dispatch_orders', 'payment_status')) {
                    $table->string('payment_status')->default('pending');
                }
                if (!Schema::hasColumn('dispatch_orders', 'payment_due_date')) {
                    $table->date('payment_due_date')->nullable();
                }
                if (!Schema::hasColumn('dispatch_orders', 'admin_notes')) {
                    $table->text('admin_notes')->nullable();
                }
            });
        }

        // Add columns to pickup_requests
        if (Schema::hasTable('pickup_requests')) {
            Schema::table('pickup_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('pickup_requests', 'tracking_id')) {
                    $table->string('tracking_id')->unique()->nullable();
                }
                if (!Schema::hasColumn('pickup_requests', 'invoice_no')) {
                    $table->string('invoice_no')->unique()->nullable();
                }
                if (!Schema::hasColumn('pickup_requests', 'tax_amount')) {
                    $table->decimal('tax_amount', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('pickup_requests', 'grand_total')) {
                    $table->decimal('grand_total', 10, 2)->default(0);
                }
            });
        }

        // Create transactions table
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_id')->unique();
                $table->string('receipt_no')->unique();
                $table->morphs('transactionable');
                $table->foreignId('user_id')->constrained('users');
                $table->decimal('amount', 10, 2);
                $table->decimal('tax', 10, 2)->default(0);
                $table->string('payment_method');
                $table->string('payment_status');
                $table->json('payment_details')->nullable();
                $table->timestamps();
            });
        }

        // Create notification_logs table
        if (!Schema::hasTable('notification_logs')) {
            Schema::create('notification_logs', function (Blueprint $table) {
                $table->id();
                $table->morphs('notifiable');
                $table->string('type');
                $table->string('recipient');
                $table->string('subject');
                $table->text('content');
                $table->string('status');
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('dispatch_orders')) {
            Schema::table('dispatch_orders', function (Blueprint $table) {
                $columns = ['tracking_id', 'invoice_no', 'tax_amount', 'grand_total', 'payment_status', 'payment_due_date', 'admin_notes'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('dispatch_orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('pickup_requests')) {
            Schema::table('pickup_requests', function (Blueprint $table) {
                $columns = ['tracking_id', 'invoice_no', 'tax_amount', 'grand_total'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('pickup_requests', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('transactions');
        Schema::dropIfExists('notification_logs');
    }
};