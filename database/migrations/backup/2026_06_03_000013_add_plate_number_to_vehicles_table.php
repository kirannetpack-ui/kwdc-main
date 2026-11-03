<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'plate_number')) {
                $table->string('plate_number')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'model')) {
                $table->string('model')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'capacity')) {
                $table->decimal('capacity', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'color')) {
                $table->string('color')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'year')) {
                $table->integer('year')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'insurance_valid_until')) {
                $table->date('insurance_valid_until')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'fitness_valid_until')) {
                $table->date('fitness_valid_until')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'status')) {
                $table->string('status')->default('available');
            }
        });
    }

    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'plate_number', 'model', 'capacity', 'color', 'year',
                'insurance_valid_until', 'fitness_valid_until', 'status'
            ]);
        });
    }
};