<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_requests', function (Blueprint $table) {
            $table->foreignId('renewal_of_id')->nullable()->constrained('warehouse_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_requests', fn (Blueprint $table) => $table->dropConstrainedForeignId('renewal_of_id'));
    }
};
