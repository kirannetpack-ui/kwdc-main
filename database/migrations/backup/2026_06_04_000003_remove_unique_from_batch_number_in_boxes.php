<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('boxes', function (Blueprint $table) {
            // Drop the unique constraint from batch_number
            $table->dropUnique(['batch_number']);
            
            // Add index instead for faster queries
            $table->index('batch_number');
        });
    }

    public function down()
    {
        Schema::table('boxes', function (Blueprint $table) {
            $table->dropIndex(['batch_number']);
            $table->unique('batch_number');
        });
    }
};