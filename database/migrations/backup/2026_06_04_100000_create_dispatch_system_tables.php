<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First, drop dependent tables if they exist
        Schema::disableForeignKeyConstraints();
        
        if (Schema::hasTable('order_items')) {
            Schema::drop('order_items');
        }
        if (Schema::hasTable('delivery_stops')) {
            Schema::drop('delivery_stops');
        }
        if (Schema::hasTable('dispatch_items')) {
            Schema::drop('dispatch_items');
        }
        if (Schema::hasTable('dispatch_orders')) {
            Schema::drop('dispatch_orders');
        }
        
        Schema::enableForeignKeyConstraints();
        
        // Create dispatch_orders table - using unsignedBigInteger instead of foreignId constraints
        Schema::create('dispatch_orders', function (Blueprint $table) {
            $table->id();
            $table->string('dispatch_number')->unique();
            
            // Use unsignedBigInteger for foreign keys (constraints added later)
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('driver_rate_id')->nullable();
            $table->unsignedBigInteger('warehouse_request_id')->nullable();
            
            // Pickup Details
            $table->text('pickup_address');
            $table->decimal('pickup_latitude', 10, 8)->nullable();
            $table->decimal('pickup_longitude', 11, 8)->nullable();
            
            // Delivery Details
            $table->text('delivery_address')->nullable();
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();
            
            // Distance & Pricing
            $table->decimal('distance_km', 10, 2)->nullable();
            $table->decimal('base_price', 12, 2)->nullable();
            $table->decimal('admin_margin', 12, 2)->nullable();
            $table->decimal('driver_earning', 12, 2)->nullable();
            
            // Invoice & Documents
            $table->string('invoice_number')->nullable();
            $table->string('bill_type')->default('regular');
            $table->string('pan_number')->nullable();
            $table->string('bill_document')->nullable();
            $table->string('packing_list')->nullable();
            $table->string('insurance_document')->nullable();
            $table->string('delivery_confirmation')->nullable();
            $table->json('other_documents')->nullable();
            
            // Boxes
            $table->integer('total_boxes')->default(0);
            
            // Status
            $table->string('status')->default('pending');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('on_the_way_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // Ratings
            $table->integer('client_rating')->nullable();
            $table->text('client_feedback')->nullable();
            
            $table->timestamps();
        });
        
        // Add foreign keys after all tables are created
        Schema::table('dispatch_orders', function (Blueprint $table) {
            // Check if referenced tables exist before adding foreign keys
            if (Schema::hasTable('users')) {
                $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            }
            
            if (Schema::hasTable('driver_rates')) {
                $table->foreign('driver_rate_id')->references('id')->on('driver_rates')->onDelete('set null');
            }
            
            if (Schema::hasTable('warehouse_requests')) {
                $table->foreign('warehouse_request_id')->references('id')->on('warehouse_requests')->onDelete('set null');
            }
        });
        
        // Create delivery_stops table
        Schema::create('delivery_stops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dispatch_order_id');
            $table->integer('stop_order');
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('boxes_count')->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
        
        // Add foreign key for delivery_stops
        Schema::table('delivery_stops', function (Blueprint $table) {
            $table->foreign('dispatch_order_id')->references('id')->on('dispatch_orders')->onDelete('cascade');
        });
        
        // Create dispatch_items table
        Schema::create('dispatch_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dispatch_order_id');
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->timestamps();
        });
        
        // Add foreign keys for dispatch_items
        Schema::table('dispatch_items', function (Blueprint $table) {
            $table->foreign('dispatch_order_id')->references('id')->on('dispatch_orders')->onDelete('cascade');
            if (Schema::hasTable('stocks')) {
                $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('dispatch_items');
        Schema::dropIfExists('delivery_stops');
        Schema::dropIfExists('dispatch_orders');
        Schema::enableForeignKeyConstraints();
    }
};