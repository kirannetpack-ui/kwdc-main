<?php

namespace Tests\Feature;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StockDocumentPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_documents_are_stored_on_private_disk(): void
    {
        Storage::fake('private_uploads');
        Storage::fake('public');

        $client = User::factory()->create([
            'role' => 'client',
            'user_code' => 'CLT-2026-0001',
        ]);

        $response = $this->actingAs($client)->post(route('stock.store'), [
            'product_name' => 'Private Stock',
            'unit' => 'pcs',
            'number_of_boxes' => 2,
            'quantity_per_box' => 5,
            'invoice_number' => 'INV-PRIVATE-001',
            'invoice_file' => UploadedFile::fake()->create('invoice.pdf', 10, 'application/pdf'),
            'grn_file' => UploadedFile::fake()->create('grn.pdf', 10, 'application/pdf'),
            'quality_certificate' => UploadedFile::fake()->create('quality.pdf', 10, 'application/pdf'),
            'other_documents' => UploadedFile::fake()->create('other.pdf', 10, 'application/pdf'),
        ]);

        $stock = Stock::where('client_code', $client->user_code)->firstOrFail();

        $response->assertRedirect(route('stock.show', $stock->id));

        Storage::disk('private_uploads')->assertExists($stock->invoice_file_path);
        Storage::disk('private_uploads')->assertExists($stock->grn_file_path);
        Storage::disk('private_uploads')->assertExists($stock->quality_certificate_path);
        Storage::disk('private_uploads')->assertExists($stock->other_documents_path);

        Storage::disk('public')->assertMissing($stock->invoice_file_path);
        $this->assertStringStartsWith('stock-documents/invoices/', $stock->invoice_file_path);
    }

    public function test_only_stock_owner_can_download_private_stock_documents(): void
    {
        Storage::fake('private_uploads');

        $client = User::factory()->create([
            'role' => 'client',
            'user_code' => 'CLT-2026-0002',
        ]);
        $otherClient = User::factory()->create([
            'role' => 'client',
            'user_code' => 'CLT-2026-0003',
        ]);

        $path = 'stock-documents/invoices/client-invoice.pdf';
        Storage::disk('private_uploads')->put($path, 'private stock invoice');

        $stock = Stock::create([
            'product_name' => 'Owned Stock',
            'user_id' => $client->id,
            'unit' => 'pcs',
            'number_of_boxes' => 1,
            'quantity_per_box' => 10,
            'total_quantity' => 10,
            'remaining_quantity' => 10,
            'batch_id' => 'BID-CLT-2026-0001',
            'sku' => 'PROD-2026-0001',
            'invoice_file_path' => $path,
            'client_code' => $client->user_code,
            'client_name' => $client->name,
            'received_date' => now(),
            'status' => 'in_stock',
        ]);

        $this->actingAs($client)
            ->get(route('stock.download-document', ['id' => $stock->id, 'type' => 'invoice']))
            ->assertOk();

        $this->actingAs($otherClient)
            ->get(route('stock.download-document', ['id' => $stock->id, 'type' => 'invoice']))
            ->assertNotFound();
    }
}
