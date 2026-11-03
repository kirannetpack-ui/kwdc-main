<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('vehicle_photo')->nullable();
            $table->string('registration_doc')->nullable();
            $table->string('insurance_doc')->nullable();
            $table->string('fitness_doc')->nullable();
            $table->string('pollution_doc')->nullable();
            $table->string('license_doc')->nullable();
            $table->date('pollution_valid_until')->nullable();
            $table->date('license_valid_until')->nullable();
        });
    }

    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_photo', 'registration_doc', 'insurance_doc',
                'fitness_doc', 'pollution_doc', 'license_doc',
                'pollution_valid_until', 'license_valid_until'
            ]);
        });
    }
};