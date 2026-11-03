<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // List all columns that should exist
            $columns = [
                'driver_id', 'owner_id', 'type', 'registration_number', 'plate_number',
                'model', 'capacity', 'color', 'year', 'insurance_valid_until',
                'fitness_valid_until', 'status'
            ];
            
            foreach ($columns as $column) {
                if (!Schema::hasColumn('vehicles', $column)) {
                    if (in_array($column, ['driver_id', 'owner_id'])) {
                        $table->foreignId($column)->nullable()->constrained('users')->onDelete('cascade');
                    } elseif (in_array($column, ['year'])) {
                        $table->integer($column)->nullable();
                    } elseif (in_array($column, ['capacity'])) {
                        $table->decimal($column, 10, 2)->nullable();
                    } elseif (in_array($column, ['insurance_valid_until', 'fitness_valid_until'])) {
                        $table->date($column)->nullable();
                    } elseif (in_array($column, ['type', 'registration_number', 'plate_number', 'model', 'color', 'status'])) {
                        $table->string($column)->nullable();
                    }
                }
            }
        });
    }

    public function down()
    {
        // No down needed
    }
};