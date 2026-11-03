<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ============================================================
        // AUTH & USERS TABLES
        // ============================================================
        
        // Users table
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('user_code')->unique()->nullable();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('phone')->nullable();
                $table->string('role')->default('client');
                $table->boolean('is_active')->default(true);
                $table->decimal('avg_rating', 3, 1)->default(0);
                $table->text('address')->nullable();
                $table->string('profile_photo')->nullable();
                $table->boolean('is_admin')->default(false);
                $table->boolean('is_driver')->default(false);
                $table->boolean('is_equipment_owner')->default(false);
                $table->string('user_type')->nullable();
                $table->text('preferred_location')->nullable();
                $table->rememberToken();
                $table->timestamps();
                
                $table->index('role');
                $table->index('email');
                $table->index('phone');
            });
        }

        // Password Reset Tokens
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // Sessions
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        // Personal Access Tokens
        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        // Cache
        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
        }

        // Jobs
        if (!Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        // ============================================================
        // USER CONTACTS
        // ============================================================
        if (!Schema::hasTable('user_contacts')) {
            Schema::create('user_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('relationship')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
            });
        }

        // ============================================================
        // DRIVER RATES
        // ============================================================
        if (!Schema::hasTable('driver_rates')) {
            Schema::create('driver_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('driver_id')->nullable()->constrained('users');
                $table->date('date');
                $table->json('rate_tiers')->nullable();
                $table->decimal('base_price', 10, 2)->default(0);
                $table->string('vehicle_type', 50)->default('Standard');
                $table->string('status')->default('active');
                $table->boolean('is_active')->default(true);
                $table->timestamp('valid_until')->nullable();
                $table->timestamp('effective_from')->nullable();
                $table->timestamp('effective_until')->nullable();
                $table->timestamp('effective_to')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'date']);
                $table->index('date');
                $table->index('status');
            });
        }

        // ============================================================
        // WAREHOUSE TABLES
        // ============================================================
        
        // Warehouses
        if (!Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('location');
                $table->text('address')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('contact_number')->nullable();
                $table->string('email')->nullable();
                $table->decimal('area_sqft', 12, 2)->nullable();
                $table->decimal('area_sqm', 12, 2)->nullable();
                $table->decimal('price_per_sqft', 12, 2)->nullable();
                $table->text('description')->nullable();
                
                // Security features
                $table->integer('cctv_count')->default(0);
                $table->integer('guards_count')->default(0);
                $table->integer('fire_extinguishers')->default(0);
                $table->json('cctv_stream_urls')->nullable();
                
                // Nearby facilities
                $table->string('nearby_police')->nullable();
                $table->string('nearby_fire')->nullable();
                $table->string('nearby_hospital')->nullable();
                $table->string('nearby_bank')->nullable();
                $table->string('nearby_fuel')->nullable();
                $table->string('nearby_market')->nullable();
                
                // Cold storage
                $table->boolean('cold_storage')->default(false);
                $table->decimal('temperature_min', 5, 2)->nullable();
                $table->decimal('temperature_max', 5, 2)->nullable();
                $table->boolean('humidity_control')->default(false);
                
                // Kataho fields
                $table->string('kataho_code')->nullable();
                $table->string('grid_id')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('country')->default('Nepal');
                
                // Additional facilities
                $table->boolean('insurance_available')->default(false);
                $table->boolean('loading_dock')->default(false);
                $table->boolean('office_space')->default(false);
                $table->boolean('staff_quarters')->default(false);
                $table->integer('parking_spaces')->default(0);
                $table->string('parking_type')->default('open');
                $table->date('available_from')->nullable();
                $table->integer('minimum_rental_period')->default(1);
                $table->text('special_notes')->nullable();
                $table->json('facilities')->nullable();
                
                // Media
                $table->string('front_image')->nullable();
                $table->string('interior_image')->nullable();
                $table->string('exterior_image')->nullable();
                
                // Documents
                $table->string('ownership_document')->nullable();
                $table->string('tax_document')->nullable();
                $table->string('fire_safety_document')->nullable();
                $table->string('building_approval_document')->nullable();
                
                // Status
                $table->string('status')->default('pending');
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->text('rejection_reason')->nullable();
                
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('status');
                $table->index('created_at');
            });
        }

        // Warehouse Photos
        if (!Schema::hasTable('warehouse_photos')) {
            Schema::create('warehouse_photos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
                $table->string('photo_path');
                $table->string('photo_type')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
            });
        }

        // Warehouse Documents
        if (!Schema::hasTable('warehouse_documents')) {
            Schema::create('warehouse_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
                $table->string('document_path');
                $table->string('document_type');
                $table->string('document_name')->nullable();
                $table->timestamps();
            });
        }

        // Warehouse Requests
        if (!Schema::hasTable('warehouse_requests')) {
            Schema::create('warehouse_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('users');
                $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->decimal('space_required', 12, 2)->nullable();
                $table->text('purpose')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->text('admin_notes')->nullable();
                $table->string('preferred_warehouse')->nullable();
                $table->string('equipment_type')->nullable();
                $table->decimal('agreed_price', 12, 2)->nullable();
                $table->json('pricing_details')->nullable();
                $table->timestamps();
                
                $table->index('client_id');
                $table->index('warehouse_id');
                $table->index('status');
            });
        }

        // ============================================================
        // VEHICLE TABLES
        // ============================================================
        
        // Vehicles
        if (!Schema::hasTable('vehicles')) {
            Schema::create('vehicles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users');
                $table->foreignId('driver_id')->nullable()->constrained('users');
                $table->string('driver_code')->nullable();
                $table->string('vehicle_number');
                $table->string('vehicle_type');
                $table->string('custom_vehicle_type')->nullable();
                $table->decimal('capacity', 10, 2);
                $table->string('capacity_unit');
                $table->string('manufacturer')->nullable();
                $table->string('model')->nullable();
                $table->integer('year')->nullable();
                $table->string('color')->nullable();
                $table->string('fuel_type')->nullable();
                $table->string('registration_number')->nullable();
                $table->date('registration_date')->nullable();
                $table->string('insurance_number')->nullable();
                $table->date('insurance_valid_until')->nullable();
                $table->string('fitness_certificate_number')->nullable();
                $table->date('fitness_valid_until')->nullable();
                $table->string('pollution_certificate_number')->nullable();
                $table->date('pollution_valid_until')->nullable();
                $table->string('permit_number')->nullable();
                $table->date('permit_valid_until')->nullable();
                $table->string('blue_book_number')->nullable();
                $table->string('insurance_file_path')->nullable();
                $table->string('fitness_file_path')->nullable();
                $table->string('pollution_file_path')->nullable();
                $table->string('permit_file_path')->nullable();
                $table->string('blue_book_file_path')->nullable();
                $table->string('front_photo_path')->nullable();
                $table->string('back_photo_path')->nullable();
                $table->string('left_photo_path')->nullable();
                $table->string('right_photo_path')->nullable();
                $table->string('interior_photo_path')->nullable();
                $table->string('status')->default('pending');
                $table->boolean('is_verified')->default(false);
                $table->foreignId('verified_by')->nullable()->constrained('users');
                $table->timestamp('verified_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->index('driver_id');
                $table->index('status');
                $table->index('is_verified');
            });
        }

        // ============================================================
        // DISPATCH TABLES
        // ============================================================
        
        // Dispatch Orders
        if (!Schema::hasTable('dispatch_orders')) {
            Schema::create('dispatch_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('users');
                $table->foreignId('driver_id')->nullable()->constrained('users');
                $table->foreignId('warehouse_id')->nullable()->constrained();
                $table->foreignId('warehouse_request_id')->nullable()->constrained();
                $table->foreignId('driver_rate_id')->nullable()->constrained('driver_rates');
                $table->string('tracking_id')->unique()->nullable();
                $table->string('invoice_no')->unique()->nullable();
                $table->text('pickup_address');
                $table->decimal('pickup_latitude', 10, 8)->nullable();
                $table->decimal('pickup_longitude', 11, 8)->nullable();
                $table->string('pickup_contact_person')->nullable();
                $table->string('pickup_contact_phone')->nullable();
                $table->decimal('total_distance', 10, 2)->default(0);
                $table->decimal('base_price', 12, 2)->default(0);
                $table->decimal('driver_earning', 12, 2)->nullable();
                $table->decimal('admin_margin', 12, 2)->nullable();
                $table->decimal('tax_amount', 10, 2)->default(0);
                $table->decimal('grand_total', 10, 2)->default(0);
                $table->string('bill_type')->default('regular');
                $table->string('pan_number')->nullable();
                $table->string('payment_status')->default('pending');
                $table->date('payment_due_date')->nullable();
                $table->date('payment_date')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamp('on_the_way_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->integer('client_rating')->nullable();
                $table->text('client_feedback')->nullable();
                $table->string('bill_document')->nullable();
                $table->string('packing_list')->nullable();
                $table->string('insurance_document')->nullable();
                $table->string('delivery_confirmation')->nullable();
                $table->json('other_documents')->nullable();
                $table->decimal('current_latitude', 10, 8)->nullable();
                $table->decimal('current_longitude', 11, 8)->nullable();
                $table->timestamp('last_location_update')->nullable();
                $table->boolean('tracking_enabled')->default(false);
                $table->string('tracking_token')->nullable();
                $table->integer('total_boxes')->default(0);
                $table->text('admin_notes')->nullable();
                $table->string('accepted_by_client_status')->nullable();
                $table->timestamps();
                
                $table->index('client_id');
                $table->index('driver_id');
                $table->index('status');
                $table->index('tracking_id');
                $table->index('created_at');
            });
        }

        // Delivery Stops
        if (!Schema::hasTable('delivery_stops')) {
            Schema::create('delivery_stops', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dispatch_order_id')->constrained()->onDelete('cascade');
                $table->integer('stop_number');
                $table->text('address');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('recipient_name');
                $table->string('recipient_phone');
                $table->string('recipient_email')->nullable();
                $table->decimal('distance_from_previous', 10, 2)->default(0);
                $table->decimal('distance_price', 10, 2)->default(0);
                $table->text('notes')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('delivered_at')->nullable();
                $table->string('proof_image')->nullable();
                $table->text('signature')->nullable();
                $table->string('invoice_document')->nullable();
                $table->timestamps();
                
                $table->index('dispatch_order_id');
                $table->index('status');
            });
        }

        // Order Items
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dispatch_order_id')->constrained()->onDelete('cascade');
                $table->string('item_name');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('total_price', 10, 2)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // ============================================================
        // PICKUP TABLES
        // ============================================================
        
        // Pickup Requests
        if (!Schema::hasTable('pickup_requests')) {
            Schema::create('pickup_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('users');
                $table->foreignId('driver_id')->nullable()->constrained('users');
                $table->foreignId('warehouse_id')->nullable()->constrained();
                
                // Tracking
                $table->string('tracking_id')->unique()->nullable();
                $table->string('invoice_no')->unique()->nullable();
                
                // Details
                $table->decimal('total_distance', 10, 2)->default(0);
                $table->decimal('total_price', 10, 2)->default(0);
                $table->decimal('tax_amount', 10, 2)->default(0);
                $table->decimal('grand_total', 10, 2)->default(0);
                $table->string('bill_type')->default('regular');
                $table->string('pan_number')->nullable();
                $table->string('payment_status')->default('pending');
                $table->date('payment_due_date')->nullable();
                
                // Status
                $table->string('status')->default('pending');
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                
                // Admin margin
                $table->decimal('admin_margin', 10, 2)->default(0);
                
                $table->timestamps();
                
                $table->index('client_id');
                $table->index('driver_id');
                $table->index('status');
                $table->index('tracking_id');
            });
        }

        // Pickup Stops
        if (!Schema::hasTable('pickup_stops')) {
            Schema::create('pickup_stops', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pickup_request_id')->constrained()->onDelete('cascade');
                $table->integer('stop_number');
                $table->text('address');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('contact_name');
                $table->string('contact_phone');
                $table->text('items_description')->nullable();
                $table->decimal('estimated_weight', 10, 2)->default(0);
                $table->decimal('distance_price', 10, 2)->default(0);
                $table->string('status')->default('pending');
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamps();
                
                $table->index('pickup_request_id');
                $table->index('status');
            });
        }

        // ============================================================
        // INVENTORY TABLES
        // ============================================================
        
        // Stocks
        if (!Schema::hasTable('stocks')) {
            Schema::create('stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users');
                $table->foreignId('client_id')->nullable()->constrained('users');
                $table->foreignId('warehouse_request_id')->nullable()->constrained();
                $table->foreignId('warehouse_id')->nullable()->constrained();
                
                // Product details
                $table->string('product_name');
                $table->text('description')->nullable();
                $table->string('unit')->default('pieces');
                $table->integer('number_of_boxes')->default(0);
                $table->integer('quantity_per_box')->default(0);
                $table->integer('total_quantity')->default(0);
                $table->integer('remaining_quantity')->nullable();
                
                // Tracking
                $table->string('batch_id')->unique();
                $table->string('sku')->unique();
                $table->string('invoice_number')->nullable();
                $table->string('invoice_file_path')->nullable();
                $table->string('warehouse_name')->nullable();
                
                // Client info
                $table->string('client_code');
                $table->string('client_name');
                
                // Documents
                $table->string('grn_file_path')->nullable();
                $table->string('quality_certificate_path')->nullable();
                $table->string('other_documents_path')->nullable();
                
                // Dates
                $table->date('manufacturing_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->date('received_date');
                
                // QR Code
                $table->string('qr_code_path')->nullable();
                $table->text('qr_code_data')->nullable();
                $table->string('qr_code')->nullable();
                
                // Pricing
                $table->decimal('purchase_price', 10, 2)->nullable();
                $table->decimal('selling_price', 10, 2)->nullable();
                
                // Status
                $table->enum('status', ['in_stock', 'partial', 'dispatched', 'expired'])->default('in_stock');
                
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('batch_id');
                $table->index('sku');
                $table->index('status');
            });
        }

        // Boxes (QR Tracking)
        if (!Schema::hasTable('boxes')) {
            Schema::create('boxes', function (Blueprint $table) {
                $table->id();
                $table->string('batch_number')->nullable()->index();
                $table->string('box_number');
                $table->string('qr_code')->unique();
                $table->text('qr_code_data')->nullable();
                $table->string('barcode')->nullable()->index();
                $table->date('entry_date')->nullable();
                $table->string('invoice_number')->nullable();
                $table->string('shipper_name')->nullable();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->integer('total_boxes')->default(1);
                $table->string('status')->default('pending');
                $table->foreignId('stock_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('dispatch_order_id')->nullable()->constrained();
                $table->foreignId('client_id')->nullable()->constrained('users');
                $table->text('notes')->nullable();
                $table->string('received_by')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->text('description')->nullable();
                $table->string('invoice_document')->nullable();
                $table->string('packing_list_document')->nullable();
                $table->string('insurance_document')->nullable();
                $table->json('other_documents')->nullable();
                $table->json('documents')->nullable();
                $table->timestamps();
                
                $table->index('qr_code');
                $table->index('status');
                $table->index('client_id');
                $table->index('warehouse_id');
            });
        }

        // ============================================================
        // EQUIPMENT TABLES
        // ============================================================
        
        // Equipment
        if (!Schema::hasTable('equipment')) {
            Schema::create('equipment', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users');
                $table->foreignId('owner_id')->nullable()->constrained('users');
                $table->string('name');
                $table->string('type');
                $table->string('model')->nullable();
                $table->integer('year')->nullable();
                $table->text('description')->nullable();
                
                // Specifications
                $table->decimal('weight', 10, 2)->nullable();
                $table->decimal('engine_power', 10, 2)->nullable();
                $table->decimal('bucket_capacity', 10, 2)->nullable();
                $table->decimal('max_reach', 10, 2)->nullable();
                
                // Pricing
                $table->decimal('daily_rate', 10, 2)->nullable();
                $table->decimal('weekly_rate', 10, 2)->nullable();
                $table->decimal('monthly_rate', 10, 2)->nullable();
                $table->decimal('security_deposit', 10, 2)->nullable();
                
                // Location
                $table->string('location')->nullable();
                $table->string('status')->default('available');
                
                // Photos
                $table->string('front_photo')->nullable();
                $table->string('side_photo')->nullable();
                $table->string('working_photo')->nullable();
                
                // Documents
                $table->string('registration_doc')->nullable();
                $table->string('insurance_doc')->nullable();
                
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('status');
            });
        }

        // Equipment Jobs
        if (!Schema::hasTable('equipment_jobs')) {
            Schema::create('equipment_jobs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('equipment_id')->nullable()->constrained('equipment');
                $table->foreignId('owner_id')->nullable()->constrained('users');
                $table->foreignId('client_id')->nullable()->constrained('users');
                $table->foreignId('equipment_owner_id')->nullable()->constrained('users');
                
                // Job details
                $table->string('job_type')->nullable();
                $table->text('pickup_location')->nullable();
                $table->text('delivery_location')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->text('description')->nullable();
                $table->string('location')->nullable();
                
                // Pricing
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('proposed_price', 10, 2)->nullable();
                $table->decimal('amount', 10, 2)->default(0);
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->decimal('client_counter_price', 10, 2)->nullable();
                
                // Messages
                $table->text('proposal_message')->nullable();
                $table->text('client_message')->nullable();
                
                // Status
                $table->string('status')->default('pending');
                $table->string('accepted_by_client_status')->nullable();
                $table->string('accepted_by_owner_status')->nullable();
                
                // Timestamps
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('proposed_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('request_date')->nullable();
                $table->timestamp('completion_date')->nullable();
                
                $table->timestamps();
                
                $table->index('owner_id');
                $table->index('client_id');
                $table->index('status');
                $table->index('equipment_id');
            });
        }

        // Equipment Requests
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
        }

        // ============================================================
        // INVOICE & FINANCE TABLES
        // ============================================================
        
        // Invoices
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users');
                $table->foreignId('client_id')->nullable()->constrained('users');
                $table->foreignId('warehouse_request_id')->constrained('warehouse_requests');
                $table->string('invoice_number')->unique();
                $table->string('order_type')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                
                // Financial
                $table->decimal('amount', 10, 2);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('discount', 12, 2)->default(0);
                $table->decimal('tax_rate', 5, 2)->default(13);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('grand_total', 12, 2)->default(0);
                
                // Billing
                $table->string('billing_type')->default('regular');
                $table->string('pan_number')->nullable();
                $table->text('billing_address')->nullable();
                $table->json('items')->nullable();
                
                // Status
                $table->string('status')->default('pending');
                $table->string('payment_status')->default('unpaid');
                $table->date('due_date');
                $table->date('payment_due_date')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->string('payment_method')->nullable();
                
                // QR Code
                $table->string('qr_code')->nullable();
                $table->text('description')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('invoice_number');
                $table->index('status');
                $table->index('payment_status');
            });
        }

        // Transactions
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transactionable_type')->nullable();
                $table->unsignedBigInteger('transactionable_id')->nullable();
                $table->foreignId('invoice_id')->constrained('invoices');
                $table->foreignId('user_id')->constrained('users');
                $table->decimal('amount', 10, 2);
                $table->decimal('tax', 10, 2)->default(0);
                $table->string('payment_method');
                $table->string('transaction_id')->unique();
                $table->string('receipt_no')->unique()->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('payment_date')->nullable();
                $table->json('payment_details')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('transaction_id');
                $table->index('status');
            });
        }

        // Margin Tiers (Correct structure – no need for separate migration)
        if (!Schema::hasTable('margin_tiers')) {
            Schema::create('margin_tiers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('min_distance', 10, 2)->default(0);
                $table->decimal('max_distance', 10, 2)->nullable();
                $table->string('service_type')->default('dispatch'); // dispatch, pickup, warehouse, equipment
                $table->string('margin_type')->default('percentage'); // percentage, flat
                $table->decimal('margin_value', 10, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('is_active');
            });
        }

        // Price Negotiations
        if (!Schema::hasTable('price_negotiations')) {
            Schema::create('price_negotiations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_request_id')->constrained();
                $table->foreignId('proposed_by')->constrained('users');
                $table->decimal('proposed_price', 12, 2);
                $table->text('message')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();
            });
        }

        // Auctions
        if (!Schema::hasTable('auctions')) {
            Schema::create('auctions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_request_id')->constrained();
                $table->decimal('starting_price', 12, 2);
                $table->decimal('current_price', 12, 2);
                $table->timestamp('end_time');
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        // ============================================================
        // PARTNER/JOB TABLES
        // ============================================================
        
        // Partner Job Offers
        if (!Schema::hasTable('partner_job_offers')) {
            Schema::create('partner_job_offers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('partner_id')->constrained('users');
                $table->foreignId('job_id')->constrained('dispatch_orders');
                $table->decimal('offered_price', 10, 2);
                $table->string('status')->default('pending');
                $table->timestamp('responded_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Partner Proposals
        if (!Schema::hasTable('partner_proposals')) {
            Schema::create('partner_proposals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('partner_id')->constrained('users');
                $table->foreignId('job_id')->constrained('dispatch_orders');
                $table->decimal('proposed_price', 10, 2);
                $table->decimal('counter_offer', 10, 2)->nullable();
                $table->text('message')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();
            });
        }

        // ============================================================
        // PROPOSAL TABLES
        // ============================================================
        
        // Proposals
        if (!Schema::hasTable('proposals')) {
            Schema::create('proposals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('users');
                $table->foreignId('driver_id')->nullable()->constrained('users');
                $table->foreignId('warehouse_request_id')->nullable()->constrained();
                $table->foreignId('warehouse_id')->nullable()->constrained();
                
                // Job details
                $table->string('job_type')->nullable();
                $table->unsignedBigInteger('job_id')->nullable();
                $table->string('job_reference')->nullable();
                
                // Pricing
                $table->decimal('proposed_price', 10, 2);
                $table->decimal('counter_price', 10, 2)->nullable();
                $table->decimal('client_counter_price', 10, 2)->nullable();
                $table->decimal('negotiated_price', 10, 2)->nullable();
                $table->decimal('final_price', 10, 2)->nullable();
                
                // Messages
                $table->text('message')->nullable();
                $table->text('client_message')->nullable();
                $table->text('negotiation_message')->nullable();
                $table->text('description')->nullable();
                $table->text('notes')->nullable();
                
                // Dates
                $table->timestamp('valid_until')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->timestamp('driver_response_at')->nullable();
                $table->timestamp('client_response_at')->nullable();
                
                // Status
                $table->string('status')->default('pending');
                
                $table->timestamps();
                
                $table->index(['client_id', 'status']);
                $table->index(['driver_id', 'status']);
                $table->index('status');
                $table->index('job_type');
            });
        }

        // ============================================================
        // INSURANCE TABLES
        // ============================================================
        
        // Insurances
        if (!Schema::hasTable('insurances')) {
            Schema::create('insurances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained();
                $table->string('insurance_type');
                $table->string('policy_number')->unique();
                $table->string('provider');
                $table->decimal('premium', 10, 2);
                $table->decimal('coverage_amount', 12, 2);
                $table->date('start_date');
                $table->date('end_date');
                $table->string('status')->default('active');
                $table->text('notes')->nullable();
                $table->string('document_path')->nullable();
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('policy_number');
                $table->index('status');
            });
        }

        // ============================================================
        // NOTIFICATION TABLES
        // ============================================================
        
        // Notifications
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained();
                $table->string('type');
                $table->string('title');
                $table->text('message');
                $table->unsignedBigInteger('related_id')->nullable();
                $table->string('related_type')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('is_read');
                $table->index('created_at');
            });
        }

        // Notification Logs
        if (!Schema::hasTable('notification_logs')) {
            Schema::create('notification_logs', function (Blueprint $table) {
                $table->id();
                $table->morphs('notifiable');
                $table->string('type');
                $table->string('recipient');
                $table->string('subject');
                $table->text('content');
                $table->string('status');
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }

        // ============================================================
        // TRACKING LOCATION HISTORY
        // ============================================================
        
        if (!Schema::hasTable('dispatch_location_history')) {
            Schema::create('dispatch_location_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dispatch_id')->constrained('dispatch_orders')->onDelete('cascade');
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->decimal('accuracy', 10, 2)->nullable();
                $table->timestamp('recorded_at');
                $table->timestamps();
                
                $table->index('dispatch_id');
                $table->index('recorded_at');
            });
        }
    }

    public function down()
    {
        // Drop tables in reverse order (to handle foreign keys)
        Schema::dropIfExists('dispatch_location_history');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('insurances');
        Schema::dropIfExists('proposals');
        Schema::dropIfExists('partner_proposals');
        Schema::dropIfExists('partner_job_offers');
        Schema::dropIfExists('auctions');
        Schema::dropIfExists('price_negotiations');
        Schema::dropIfExists('margin_tiers');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('equipment_requests');
        Schema::dropIfExists('equipment_jobs');
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('driver_rates');
        Schema::dropIfExists('boxes');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('pickup_stops');
        Schema::dropIfExists('pickup_requests');
        Schema::dropIfExists('dispatch_items');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('delivery_stops');
        Schema::dropIfExists('dispatch_orders');
        Schema::dropIfExists('warehouse_tenants');
        Schema::dropIfExists('warehouse_requests');
        Schema::dropIfExists('warehouse_documents');
        Schema::dropIfExists('warehouse_photos');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('user_contacts');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
