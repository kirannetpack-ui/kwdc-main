<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users');
                $table->foreignId('client_id')->nullable()->constrained('users');
                $table->foreignId('warehouse_request_id')->constrained('warehouse_requests');
                $table->string('invoice_number')->unique();
                $table->string('order_type')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                
                // Financial
                $table->decimal('amount', 10, 2);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('discount', 12, 2)->default(0);
                $table->decimal('tax_rate', 5, 2)->default(13);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('grand_total', 12, 2)->default(0);
                
                // Billing
                $table->string('billing_type')->default('regular');
                $table->string('pan_number')->nullable();
                $table->text('billing_address')->nullable();
                $table->json('items')->nullable();
                
                // Status
                $table->string('status')->default('pending');
                $table->string('payment_status')->default('unpaid');
                $table->date('due_date');
                $table->date('payment_due_date')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->string('payment_method')->nullable();
                
                // QR Code
                $table->string('qr_code')->nullable();
                $table->text('description')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('invoice_number');
                $table->index('status');
                $table->index('payment_status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};