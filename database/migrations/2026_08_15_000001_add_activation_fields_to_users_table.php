<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'activation_code_hash')) {
                $table->string('activation_code_hash')->nullable()->after('remember_token');
            }

            if (!Schema::hasColumn('users', 'activation_expires_at')) {
                $table->timestamp('activation_expires_at')->nullable()->after('activation_code_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'activation_code_hash')) {
                $table->dropColumn('activation_code_hash');
            }

            if (Schema::hasColumn('users', 'activation_expires_at')) {
                $table->dropColumn('activation_expires_at');
            }
        });
    }
};
