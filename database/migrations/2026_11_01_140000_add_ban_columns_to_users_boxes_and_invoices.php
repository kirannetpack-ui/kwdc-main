<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_banned')) {
                $table->boolean('is_banned')->default(false);
            }
            if (!Schema::hasColumn('users', 'ban_reason')) {
                $table->string('ban_reason')->nullable();
            }
            if (!Schema::hasColumn('users', 'banned_at')) {
                $table->timestamp('banned_at')->nullable();
            }
        });

        Schema::table('boxes', function (Blueprint $table) {
            if (!Schema::hasColumn('boxes', 'is_banned')) {
                $table->boolean('is_banned')->default(false);
            }
            if (!Schema::hasColumn('boxes', 'ban_reason')) {
                $table->string('ban_reason')->nullable();
            }
            if (!Schema::hasColumn('boxes', 'banned_at')) {
                $table->timestamp('banned_at')->nullable();
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'is_banned')) {
                $table->boolean('is_banned')->default(false);
            }
            if (!Schema::hasColumn('invoices', 'ban_reason')) {
                $table->string('ban_reason')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'banned_at')) {
                $table->timestamp('banned_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_banned')) {
                $table->dropColumn(['is_banned', 'ban_reason', 'banned_at']);
            }
        });

        Schema::table('boxes', function (Blueprint $table) {
            if (Schema::hasColumn('boxes', 'is_banned')) {
                $table->dropColumn(['is_banned', 'ban_reason', 'banned_at']);
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'is_banned')) {
                $table->dropColumn(['is_banned', 'ban_reason', 'banned_at']);
            }
        });
    }
};
