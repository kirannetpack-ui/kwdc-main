<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pickup_requests')) {
            Schema::table('pickup_requests', function (Blueprint $table) {
                // Add started_at
                if (!Schema::hasColumn('pickup_requests', 'started_at')) {
                    $table->timestamp('started_at')->nullable()->after('assigned_at');
                }
                
                // Add completed_at
                if (!Schema::hasColumn('pickup_requests', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('delivered_at');
                }
                
                // Add cancelled_at
                if (!Schema::hasColumn('pickup_requests', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('completed_at');
                }
                
                // Add notes
                if (!Schema::hasColumn('pickup_requests', 'notes')) {
                    $table->text('notes')->nullable()->after('pan_number');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pickup_requests')) {
            Schema::table('pickup_requests', function (Blueprint $table) {
                $columns = ['started_at', 'completed_at', 'cancelled_at', 'notes'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('pickup_requests', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};