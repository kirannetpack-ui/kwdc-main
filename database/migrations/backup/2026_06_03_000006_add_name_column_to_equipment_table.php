<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'type')) {
                $table->string('type')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'location')) {
                $table->string('location')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'daily_rate')) {
                $table->decimal('daily_rate', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('equipment', 'status')) {
                $table->string('status')->default('available');
            }
        });
    }

    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['name', 'type', 'location', 'daily_rate', 'status']);
        });
    }
};