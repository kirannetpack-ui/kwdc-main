<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add Kataho fields to dispatch_orders
        if (Schema::hasTable('dispatch_orders')) {
            Schema::table('dispatch_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('dispatch_orders', 'pickup_kataho_code')) {
                    $table->string('pickup_kataho_code')->nullable();
                    $table->string('delivery_kataho_code')->nullable();
                    $table->string('pickup_grid_id')->nullable();
                    $table->string('delivery_grid_id')->nullable();
                }
            });
        }

        // Add Kataho fields to pickup_requests
        if (Schema::hasTable('pickup_requests')) {
            // First, check what columns exist
            $columns = Schema::getColumnListing('pickup_requests');
            
            Schema::table('pickup_requests', function (Blueprint $table) use ($columns) {
                // Find the right column to place kataho_code after
                $afterColumn = null;
                if (in_array('pickup_address', $columns)) {
                    $afterColumn = 'pickup_address';
                } elseif (in_array('pickup_location', $columns)) {
                    $afterColumn = 'pickup_location';
                } elseif (in_array('address', $columns)) {
                    $afterColumn = 'address';
                } elseif (in_array('location', $columns)) {
                    $afterColumn = 'location';
                } else {
                    $afterColumn = 'id'; // fallback
                }
                
                if (!Schema::hasColumn('pickup_requests', 'kataho_code')) {
                    $table->string('kataho_code')->nullable()->after($afterColumn);
                }
                
                if (!Schema::hasColumn('pickup_requests', 'grid_id')) {
                    $table->string('grid_id')->nullable()->after('kataho_code');
                }
                
                // Find the right column for destination
                $destAfterColumn = null;
                if (in_array('destination_address', $columns)) {
                    $destAfterColumn = 'destination_address';
                } elseif (in_array('destination_location', $columns)) {
                    $destAfterColumn = 'destination_location';
                } elseif (in_array('delivery_address', $columns)) {
                    $destAfterColumn = 'delivery_address';
                } else {
                    $destAfterColumn = 'grid_id'; // fallback
                }
                
                if (!Schema::hasColumn('pickup_requests', 'destination_kataho_code')) {
                    $table->string('destination_kataho_code')->nullable()->after($destAfterColumn);
                }
                
                if (!Schema::hasColumn('pickup_requests', 'destination_grid_id')) {
                    $table->string('destination_grid_id')->nullable()->after('destination_kataho_code');
                }
            });
        }
    }

    public function down(): void
    {
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
        
        if (Schema::hasTable('pickup_requests')) {
            Schema::table('pickup_requests', function (Blueprint $table) {
                $table->dropColumn([
                    'kataho_code',
                    'grid_id',
                    'destination_kataho_code',
                    'destination_grid_id'
                ]);
            });
        }
    }
};