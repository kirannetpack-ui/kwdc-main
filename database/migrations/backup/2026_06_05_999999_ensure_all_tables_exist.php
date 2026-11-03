<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // This migration ensures all required tables exist
        // It checks each table and creates only if missing
        
        $tables = [
            'users',
            'password_reset_tokens',
            'sessions',
            'cache',
            'jobs',
            'warehouses',
            'warehouse_requests',
            'dispatch_orders',
            'delivery_stops',
            'pickup_requests',
            'pickup_stops',
            'stocks',
            'vehicles',
            'driver_rates',
            'equipment',
            'equipment_jobs',
            'boxes',
            'user_contacts',
            'notifications',
            'margin_tiers',
            'invoices',
            'transactions',
            'notification_logs'
        ];
        
        // Note: Most tables should already be created by their specific migrations
        // This just ensures critical tables exist to prevent errors
        
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
        
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
        
        // Add any missing columns to existing tables
        if (Schema::hasTable('equipment_jobs')) {
            if (!Schema::hasColumn('equipment_jobs', 'accepted_by_client_status')) {
                Schema::table('equipment_jobs', function (Blueprint $table) {
                    $table->string('accepted_by_client_status', 50)->nullable();
                });
            }
        }
        
        if (Schema::hasTable('dispatch_orders')) {
            if (!Schema::hasColumn('dispatch_orders', 'accepted_by_client_status')) {
                Schema::table('dispatch_orders', function (Blueprint $table) {
                    $table->string('accepted_by_client_status', 50)->nullable();
                });
            }
        }
    }

    public function down()
    {
        // Don't drop tables here as they may be needed by other migrations
        // Individual migrations handle their own down methods
    }
};