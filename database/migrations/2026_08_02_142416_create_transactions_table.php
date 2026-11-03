<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transactionable_type')->nullable();
                $table->unsignedBigInteger('transactionable_id')->nullable();
                $table->foreignId('invoice_id')->constrained('invoices');
                $table->foreignId('user_id')->constrained('users');
                $table->decimal('amount', 10, 2);
                $table->decimal('tax', 10, 2)->default(0);
                $table->string('payment_method');
                $table->string('transaction_id')->unique();
                $table->string('receipt_no')->unique()->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('payment_date')->nullable();
                $table->json('payment_details')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('transaction_id');
                $table->index('status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};