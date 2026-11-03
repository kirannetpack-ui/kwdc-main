<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Ensure pickup_requests has driver_earning
        if (Schema::hasTable('pickup_requests') && !Schema::hasColumn('pickup_requests', 'driver_earning')) {
            Schema::table('pickup_requests', function (Blueprint $table) {
                $table->decimal('driver_earning', 12, 2)->nullable()->after('grand_total');
            });
        }

        // 2. Create partner_earnings table
        if (!Schema::hasTable('partner_earnings')) {
            Schema::create('partner_earnings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('partner_id')->constrained('users')->onDelete('cascade');
                $table->string('order_type')->index(); // 'dispatch', 'pickup', 'equipment'
                $table->unsignedBigInteger('order_id')->index();
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('status')->default('pending'); // pending, paid, cancelled
                $table->timestamp('earned_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                // Composite index for fast lookups
                $table->index(['order_type', 'order_id']);
                $table->index('partner_id');
                $table->index('status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('partner_earnings');
        if (Schema::hasTable('pickup_requests') && Schema::hasColumn('pickup_requests', 'driver_earning')) {
            Schema::table('pickup_requests', function (Blueprint $table) {
                $table->dropColumn('driver_earning');
            });
        }
    }
};