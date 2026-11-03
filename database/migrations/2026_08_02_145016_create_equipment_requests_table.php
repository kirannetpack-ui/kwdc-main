<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('equipment_requests')) {
            Schema::create('equipment_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('users');
                $table->foreignId('equipment_id')->nullable()->constrained('equipment');
                $table->string('equipment_type');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('location');
                $table->text('description')->nullable();
                $table->string('status')->default('pending');
                $table->decimal('quoted_price', 10, 2)->nullable();
                $table->foreignId('assigned_equipment_id')->nullable()->constrained('equipment');
                $table->text('special_requirements')->nullable();
                $table->json('preferred_brands')->nullable();
                $table->string('budget_range')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('client_id');
                $table->index('status');
                $table->index('equipment_type');
            });
        } else {
            // Table exists, add missing columns if needed
            Schema::table('equipment_requests', function (Blueprint $table) {
                $columns = Schema::getColumnListing('equipment_requests');
                
                if (!in_array('notes', $columns)) {
                    $table->text('notes')->nullable()->after('budget_range');
                }
                
                if (!in_array('special_requirements', $columns)) {
                    $table->text('special_requirements')->nullable()->after('assigned_equipment_id');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('equipment_requests');
    }
};