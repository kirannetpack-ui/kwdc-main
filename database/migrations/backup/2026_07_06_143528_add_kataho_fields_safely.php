<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add to pickup_requests
        if (Schema::hasTable('pickup_requests')) {
            Schema::table('pickup_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('pickup_requests', 'kataho_code')) {
                    $table->string('kataho_code')->nullable();
                }
                if (!Schema::hasColumn('pickup_requests', 'grid_id')) {
                    $table->string('grid_id')->nullable();
                }
                if (!Schema::hasColumn('pickup_requests', 'destination_kataho_code')) {
                    $table->string('destination_kataho_code')->nullable();
                }
                if (!Schema::hasColumn('pickup_requests', 'destination_grid_id')) {
                    $table->string('destination_grid_id')->nullable();
                }
                if (!Schema::hasColumn('pickup_requests', 'destination_latitude')) {
                    $table->decimal('destination_latitude', 10, 8)->nullable();
                }
                if (!Schema::hasColumn('pickup_requests', 'destination_longitude')) {
                    $table->decimal('destination_longitude', 10, 8)->nullable();
                }
            });
        }

        // Add to dispatch_orders
        if (Schema::hasTable('dispatch_orders')) {
            Schema::table('dispatch_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('dispatch_orders', 'pickup_kataho_code')) {
                    $table->string('pickup_kataho_code')->nullable();
                }
                if (!Schema::hasColumn('dispatch_orders', 'delivery_kataho_code')) {
                    $table->string('delivery_kataho_code')->nullable();
                }
                if (!Schema::hasColumn('dispatch_orders', 'pickup_grid_id')) {
                    $table->string('pickup_grid_id')->nullable();
                }
                if (!Schema::hasColumn('dispatch_orders', 'delivery_grid_id')) {
                    $table->string('delivery_grid_id')->nullable();
                }
            });
        }

        // Add to pickup_stops
        if (Schema::hasTable('pickup_stops')) {
            Schema::table('pickup_stops', function (Blueprint $table) {
                if (!Schema::hasColumn('pickup_stops', 'kataho_code')) {
                    $table->string('kataho_code')->nullable();
                }
                if (!Schema::hasColumn('pickup_stops', 'kataho_grid_id')) {
                    $table->string('kataho_grid_id')->nullable();
                }
                if (!Schema::hasColumn('pickup_stops', 'latitude')) {
                    $table->decimal('latitude', 10, 8)->nullable();
                }
                if (!Schema::hasColumn('pickup_stops', 'longitude')) {
                    $table->decimal('longitude', 10, 8)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pickup_requests')) {
            Schema::table('pickup_requests', function (Blueprint $table) {
                $table->dropColumn([
                    'kataho_code',
                    'grid_id',
                    'destination_kataho_code',
                    'destination_grid_id',
                    'destination_latitude',
                    'destination_longitude'
                ]);
            });
        }

        if (Schema::hasTable('dispatch_orders')) {
            Schema::table('dispatch_orders', function (Blueprint $table) {
                $table->dropColumn([
                    'pickup_kataho_code',
                    'delivery_kataho_code',
                    'pickup_grid_id',
                    'delivery_grid_id'
                ]);
            });
        }

        if (Schema::hasTable('pickup_stops')) {
            Schema::table('pickup_stops', function (Blueprint $table) {
                $table->dropColumn([
                    'kataho_code',
                    'kataho_grid_id',
                    'latitude',
                    'longitude'
                ]);
            });
        }
    }
};