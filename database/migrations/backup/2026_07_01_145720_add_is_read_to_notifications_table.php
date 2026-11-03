<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('notifications', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('message');
            }
            
            // Add other missing columns if needed
            if (!Schema::hasColumn('notifications', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('id');
            }
            
            if (!Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->after('id');
            }
            
            if (!Schema::hasColumn('notifications', 'message')) {
                $table->text('message')->after('title');
            }
            
            if (!Schema::hasColumn('notifications', 'type')) {
                $table->string('type')->default('info')->after('message');
            }
            
            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['is_read', 'read_at']);
        });
    }
};