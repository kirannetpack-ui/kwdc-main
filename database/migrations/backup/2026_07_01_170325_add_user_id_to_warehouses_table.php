<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            // Check if user_id column exists
            if (!Schema::hasColumn('warehouses', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            
            // Check if other columns exist
            if (!Schema::hasColumn('warehouses', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            
            if (!Schema::hasColumn('warehouses', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            
            if (!Schema::hasColumn('warehouses', 'country')) {
                $table->string('country')->nullable()->after('state');
            }
            
            if (!Schema::hasColumn('warehouses', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('country');
            }
            
            if (!Schema::hasColumn('warehouses', 'area')) {
                $table->decimal('area', 10, 2)->nullable()->after('postal_code');
            }
            
            if (!Schema::hasColumn('warehouses', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('area');
            }
            
            if (!Schema::hasColumn('warehouses', 'description')) {
                $table->text('description')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id', 
                'city', 
                'state', 
                'country', 
                'postal_code', 
                'area', 
                'price', 
                'description'
            ]);
        });
    }
};