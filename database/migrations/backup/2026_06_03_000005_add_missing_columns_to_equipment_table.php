<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('equipment', 'model')) {
                $table->string('model')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'year')) {
                $table->integer('year')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'weight')) {
                $table->decimal('weight', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('equipment', 'engine_power')) {
                $table->decimal('engine_power', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('equipment', 'bucket_capacity')) {
                $table->decimal('bucket_capacity', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('equipment', 'max_reach')) {
                $table->decimal('max_reach', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('equipment', 'daily_rate')) {
                $table->decimal('daily_rate', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('equipment', 'weekly_rate')) {
                $table->decimal('weekly_rate', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('equipment', 'monthly_rate')) {
                $table->decimal('monthly_rate', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('equipment', 'security_deposit')) {
                $table->decimal('security_deposit', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('equipment', 'location')) {
                $table->string('location')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'front_photo')) {
                $table->string('front_photo')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'side_photo')) {
                $table->string('side_photo')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'working_photo')) {
                $table->string('working_photo')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'registration_doc')) {
                $table->string('registration_doc')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'insurance_doc')) {
                $table->string('insurance_doc')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn([
                'model', 'year', 'description', 'weight', 'engine_power',
                'bucket_capacity', 'max_reach', 'daily_rate', 'weekly_rate',
                'monthly_rate', 'security_deposit', 'location', 'front_photo',
                'side_photo', 'working_photo', 'registration_doc', 'insurance_doc'
            ]);
        });
    }
};