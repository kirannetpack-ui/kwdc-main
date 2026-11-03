<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add is_active column if not exists
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
            
            // Add phone column if not exists
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            
            // Add avg_rating column if not exists
            if (!Schema::hasColumn('users', 'avg_rating')) {
                $table->decimal('avg_rating', 3, 1)->default(0)->after('is_active');
            }
            
            // Add user_code column if not exists
            if (!Schema::hasColumn('users', 'user_code')) {
                $table->string('user_code')->unique()->nullable()->after('id');
            }
            
            // Add address column if not exists
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            
            // Add profile_photo column if not exists
            if (!Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('address');
            }
            
            // Add email_verified_at if not exists
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['is_active', 'phone', 'avg_rating', 'user_code', 'address', 'profile_photo'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};