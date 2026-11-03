<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            // Add client_id if missing
            if (!Schema::hasColumn('dispatch_orders', 'client_id')) {
                $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            }
            // Add dispatch_number if missing
            if (!Schema::hasColumn('dispatch_orders', 'dispatch_number')) {
                $table->string('dispatch_number')->unique()->nullable();
            }
            // Add pickup_contact fields
            if (!Schema::hasColumn('dispatch_orders', 'pickup_contact_person')) {
                $table->string('pickup_contact_person')->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'pickup_contact_phone')) {
                $table->string('pickup_contact_phone')->nullable();
            }
            // Add document fields
            if (!Schema::hasColumn('dispatch_orders', 'packing_list')) {
                $table->string('packing_list')->nullable();
            }
            if (!Schema::hasColumn('dispatch_orders', 'insurance_document')) {
                $table->string('insurance_document')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('dispatch_orders', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['client_id', 'dispatch_number', 'pickup_contact_person', 'pickup_contact_phone', 'packing_list', 'insurance_document']);
        });
    }
};