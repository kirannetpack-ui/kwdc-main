<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_rates', function (Blueprint $table) {
            // Add effective_from column after date
            if (!Schema::hasColumn('driver_rates', 'effective_from')) {
                $table->timestamp('effective_from')->nullable()->after('date');
            }
            
            // Add effective_until column after effective_from
            if (!Schema::hasColumn('driver_rates', 'effective_until')) {
                $table->timestamp('effective_until')->nullable()->after('effective_from');
            }
            
            // Add rate_per_km column (for compatibility)
            if (!Schema::hasColumn('driver_rates', 'rate_per_km')) {
                $table->decimal('rate_per_km', 10, 2)->default(0)->after('valid_until');
            }
            
            // Add rate_per_hour column (for compatibility)
            if (!Schema::hasColumn('driver_rates', 'rate_per_hour')) {
                $table->decimal('rate_per_hour', 10, 2)->default(0)->after('rate_per_km');
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_rates', function (Blueprint $table) {
            $table->dropColumn([
                'effective_from',
                'effective_until',
                'rate_per_km',
                'rate_per_hour'
            ]);
        });
    }
};