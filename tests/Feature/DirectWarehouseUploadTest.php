<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DirectWarehouseUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_r2_adapter_signs_staging_and_omits_unsupported_object_acl(): void
    {
        $request = null;
        $disk = Storage::build([
            'driver' => 'r2', 'region' => 'auto', 'key' => 'test-key', 'secret' => 'test-secret',
            'bucket' => 'private-test', 'endpoint' => 'https://storage.example.test',
            'use_path_style_endpoint' => true, 'request_checksum_calculation' => 'when_required',
            'http_handler' => function ($outgoing) use (&$request) {
                $request = $outgoing;

                return \GuzzleHttp\Promise\Create::promiseFor(new \GuzzleHttp\Psr7\Response(200, ['ETag' => '"test"']));
            },
        ]);
        $signed = $disk->temporaryUploadUrl('staged-uploads/test', now()->addMinute());
        $this->assertStringStartsWith('https://storage.example.test/private-test/staged-uploads/test?', $signed['url']);
        $this->assertTrue($disk->put('warehouses/documents/test.pdf', '%PDF-1.4'));
        $this->assertNotNull($request);
        $this->assertFalse($request->hasHeader('x-amz-acl'));
    }

    public function test_staged_warehouse_files_keep_existing_validation_ownership_and_disk_separation(): void
    {
        $private = Storage::fake('private_uploads');
        $public = Storage::fake('public');
        config([
            'filesystems.direct_uploads' => true,
            'filesystems.disks.private_uploads.driver' => 'r2',
        ]);
        $owner = User::factory()->create(['role' => 'property_owner']);
        $other = User::factory()->create(['role' => 'property_owner']);
        $imageFile = UploadedFile::fake()->image('warehouse.jpg');
        $image = file_get_contents($imageFile->getPathname());
        $document = "%PDF-1.4\n%".str_repeat('x', 5 * 1024 * 1024)."\n%%EOF\n";
        $tokens = [];
        foreach ([
            'front_image' => ['warehouse.jpg', $image],
            'ownership_document' => ['ownership.pdf', $document],
        ] as $field => [$name, $contents]) {
            $key = 'staged-uploads/'.$field;
            $private->put($key, $contents);
            $tokens[] = Crypt::encryptString(json_encode([
                'field' => $field, 'name' => $name, 'size' => strlen($contents),
                'key' => $key, 'user' => $owner->id, 'expires' => now()->addMinute()->timestamp,
            ], JSON_THROW_ON_ERROR));
        }
        $input = [
            'name' => 'Staged warehouse', 'location' => 'Kathmandu', 'contact_number' => '01-5339942',
            'area_sqft' => 1000, 'price_per_sqft' => 10, '_direct_uploads' => json_encode($tokens),
        ];
        $this->actingAs($other)->postJson(route('warehouses.store'), $input)->assertUnprocessable();
        $this->actingAs($owner)->post(route('warehouses.store'), $input)->assertSessionHasNoErrors()->assertRedirect();
        $warehouse = Warehouse::where('name', 'Staged warehouse')->firstOrFail();
        $this->assertSame($owner->id, $warehouse->user_id);
        $public->assertExists($warehouse->front_image);
        $private->assertExists($warehouse->ownership_document);
        $public->assertMissing($warehouse->ownership_document);
        $private->assertMissing('staged-uploads/ownership_document');
        $this->postJson(route('warehouses.store'), $input)->assertUnprocessable();
        $this->assertSame(1, Warehouse::count());
    }
}
