<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('user_contacts', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('user_contacts', 'relation')) {
                $table->string('relation')->nullable()->after('whatsapp');
            }
            if (!Schema::hasColumn('user_contacts', 'receive_emails')) {
                $table->boolean('receive_emails')->default(true)->after('is_primary');
            }
            if (!Schema::hasColumn('user_contacts', 'receive_sms')) {
                $table->boolean('receive_sms')->default(false)->after('receive_emails');
            }
            if (!Schema::hasColumn('user_contacts', 'receive_whatsapp')) {
                $table->boolean('receive_whatsapp')->default(false)->after('receive_sms');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_contacts', function (Blueprint $table) {
            foreach (['receive_whatsapp', 'receive_sms', 'receive_emails', 'relation', 'whatsapp'] as $column) {
                if (Schema::hasColumn('user_contacts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
