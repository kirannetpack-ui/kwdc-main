<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('dispatch_orders', function (Blueprint $table) {
        // Add delivery coordinates for ETA calculation
        // Place them after pickup_address (which exists)
        $table->decimal('delivery_lat', 10, 7)->nullable()->after('pickup_address');
        $table->decimal('delivery_lng', 11, 8)->nullable()->after('delivery_lat');
        
        // Add estimated arrival timestamp
        $table->timestamp('estimated_arrival')->nullable()->after('last_location_update');
    });
}

public function down(): void
{
    Schema::table('dispatch_orders', function (Blueprint $table) {
        $table->dropColumn(['delivery_lat', 'delivery_lng', 'estimated_arrival']);
    });
}

};