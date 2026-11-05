<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_requests', function (Blueprint $table) {
            $table->date('renewal_reminded_for')->nullable();
            $table->string('renewal_decision')->nullable();
            $table->date('renewal_decided_for')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_requests', function (Blueprint $table) {
            $table->dropColumn(['renewal_reminded_for', 'renewal_decision', 'renewal_decided_for']);
        });
    }
};
