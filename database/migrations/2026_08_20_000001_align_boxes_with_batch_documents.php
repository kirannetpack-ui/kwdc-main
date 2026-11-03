<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boxes', function (Blueprint $table) {
            if (!Schema::hasColumn('boxes', 'batch_number')) {
                $table->string('batch_number')->nullable()->index()->after('id');
            }

            if (!Schema::hasColumn('boxes', 'barcode')) {
                $table->string('barcode')->nullable()->index()->after('qr_code');
            }

            if (!Schema::hasColumn('boxes', 'entry_date')) {
                $table->date('entry_date')->nullable()->after('barcode');
            }

            if (!Schema::hasColumn('boxes', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('entry_date');
            }

            if (!Schema::hasColumn('boxes', 'shipper_name')) {
                $table->string('shipper_name')->nullable()->after('invoice_number');
            }

            if (!Schema::hasColumn('boxes', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('shipper_name')->constrained('warehouses')->nullOnDelete();
            }

            if (!Schema::hasColumn('boxes', 'total_boxes')) {
                $table->integer('total_boxes')->default(1)->after('warehouse_id');
            }

            if (!Schema::hasColumn('boxes', 'notes')) {
                $table->text('notes')->nullable()->after('client_id');
            }

            if (!Schema::hasColumn('boxes', 'received_by')) {
                $table->string('received_by')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('boxes', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('received_by');
            }

            if (!Schema::hasColumn('boxes', 'invoice_document')) {
                $table->string('invoice_document')->nullable()->after('description');
            }

            if (!Schema::hasColumn('boxes', 'packing_list_document')) {
                $table->string('packing_list_document')->nullable()->after('invoice_document');
            }

            if (!Schema::hasColumn('boxes', 'insurance_document')) {
                $table->string('insurance_document')->nullable()->after('packing_list_document');
            }

            if (!Schema::hasColumn('boxes', 'other_documents')) {
                $table->json('other_documents')->nullable()->after('insurance_document');
            }
        });

        Schema::table('boxes', function (Blueprint $table) {
            if (Schema::hasColumn('boxes', 'stock_id')) {
                $table->foreignId('stock_id')->nullable()->change();
            }

            if (Schema::hasColumn('boxes', 'qr_code_data')) {
                $table->text('qr_code_data')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('boxes', function (Blueprint $table) {
            $dropColumns = array_filter([
                Schema::hasColumn('boxes', 'batch_number') ? 'batch_number' : null,
                Schema::hasColumn('boxes', 'barcode') ? 'barcode' : null,
                Schema::hasColumn('boxes', 'entry_date') ? 'entry_date' : null,
                Schema::hasColumn('boxes', 'invoice_number') ? 'invoice_number' : null,
                Schema::hasColumn('boxes', 'shipper_name') ? 'shipper_name' : null,
                Schema::hasColumn('boxes', 'total_boxes') ? 'total_boxes' : null,
                Schema::hasColumn('boxes', 'notes') ? 'notes' : null,
                Schema::hasColumn('boxes', 'received_by') ? 'received_by' : null,
                Schema::hasColumn('boxes', 'received_at') ? 'received_at' : null,
                Schema::hasColumn('boxes', 'invoice_document') ? 'invoice_document' : null,
                Schema::hasColumn('boxes', 'packing_list_document') ? 'packing_list_document' : null,
                Schema::hasColumn('boxes', 'insurance_document') ? 'insurance_document' : null,
                Schema::hasColumn('boxes', 'other_documents') ? 'other_documents' : null,
            ]);

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }

            if (Schema::hasColumn('boxes', 'warehouse_id')) {
                $table->dropConstrainedForeignId('warehouse_id');
            }
        });
    }
};
