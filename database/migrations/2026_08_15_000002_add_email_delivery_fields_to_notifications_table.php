<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'notification_number')) {
                $table->string('notification_number')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('notifications', 'dispatch_order_id')) {
                $table->foreignId('dispatch_order_id')->nullable()->after('related_type')->constrained('dispatch_orders')->nullOnDelete();
            }
            if (!Schema::hasColumn('notifications', 'pickup_request_id')) {
                $table->foreignId('pickup_request_id')->nullable()->after('dispatch_order_id')->constrained('pickup_requests')->nullOnDelete();
            }
            if (!Schema::hasColumn('notifications', 'recipient_email')) {
                $table->string('recipient_email')->nullable()->after('pickup_request_id');
            }
            if (!Schema::hasColumn('notifications', 'recipient_name')) {
                $table->string('recipient_name')->nullable()->after('recipient_email');
            }
            if (!Schema::hasColumn('notifications', 'subject')) {
                $table->string('subject')->nullable()->after('recipient_name');
            }
            if (!Schema::hasColumn('notifications', 'status')) {
                $table->string('status')->default('created')->after('subject');
            }
            if (!Schema::hasColumn('notifications', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('notifications', 'error_message')) {
                $table->text('error_message')->nullable()->after('sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'pickup_request_id')) {
                $table->dropForeign(['pickup_request_id']);
            }

            if (Schema::hasColumn('notifications', 'dispatch_order_id')) {
                $table->dropForeign(['dispatch_order_id']);
            }

            foreach (['error_message', 'sent_at', 'status', 'subject', 'recipient_name', 'recipient_email', 'pickup_request_id', 'dispatch_order_id', 'notification_number'] as $column) {
                if (Schema::hasColumn('notifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
