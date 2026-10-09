<?php

use App\Models\Kategori;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

test('a super admin can download a dated product export and import template', function () {
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    Excel::fake();

    $this->actingAs($superAdmin, 'sanctum')
        ->get('/api/data/export')
        ->assertOk();

    Excel::assertDownloaded('export-barang-'.now()->format('Y-m-d').'.xlsx');

    $this->get('/api/data/template')->assertOk();

    Excel::assertDownloaded('template-import-barang.xlsx');
});

test('a super admin can preview and confirm a valid csv product import', function () {
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    $kategori = Kategori::factory()->create();
    $csv = implode(',', [
        'KodeItem', 'Barcode', 'SKU', 'NamaItem', 'kategori_id', 'Merek',
        'Stok', 'Rak', 'TipeItem', 'HargaBeli', 'HargaJual', 'Keterangan',
        'Foto', 'Status',
    ])."\n".implode(',', [
        'IMPORT-001', 'BAR-IMPORT-001', 'SKU-IMPORT-001', 'Tomat Segar',
        $kategori->id, 'Kebun', 12, 'A-01', 'Sayur', 3000, 5000,
        'Tomat merah', '', 'tersedia',
    ])."\n";

    $preview = $this->actingAs($superAdmin, 'sanctum')
        ->post('/api/data/import', [
            'file' => UploadedFile::fake()->createWithContent('barang.csv', $csv),
        ])
        ->assertOk()
        ->assertJsonPath('data.valid', true)
        ->assertJsonPath('data.created', 1)
        ->assertJsonPath('data.requires_confirmation', true);

    $this->assertDatabaseMissing('barang', ['KodeItem' => 'IMPORT-001']);

    $this->post('/api/data/import', [
        'file' => UploadedFile::fake()->createWithContent('barang.csv', $csv),
        'confirmed' => true,
        'preview_hash' => $preview->json('data.preview_hash'),
    ])
        ->assertOk()
        ->assertJsonPath('data.created', 1)
        ->assertJsonPath('data.requires_confirmation', false);

    $this->assertDatabaseHas('barang', [
        'KodeItem' => 'IMPORT-001',
        'Stok' => 12,
    ]);
});

test('an import with missing headers is rejected without writing products', function () {
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    $this->actingAs($superAdmin, 'sanctum')
        ->post('/api/data/import', [
            'file' => UploadedFile::fake()->createWithContent('invalid.csv', "WrongHeader\nvalue\n"),
        ])
        ->assertUnprocessable()
        ->assertJsonPath('data.valid', false)
        ->assertJsonPath('data.created', 0);

    $this->assertDatabaseCount('barang', 0);
});

test('customers cannot export or import product data', function () {
    $this->actingAs(User::factory()->create(['role' => 'customer']), 'sanctum')
        ->getJson('/api/data/export')
        ->assertForbidden();

    $this->postJson('/api/data/import', [])
        ->assertForbidden();
});
