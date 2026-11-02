<?php

use App\Models\Barang;
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

test('a super admin can preview and import products using the pos excel column format', function () {
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    $kategori = Kategori::factory()->create(['nama_kategori' => 'sayuran']);

    $csv = implode(',', [
        'KodeItem', 'Barcode', 'SKU', 'NamaItem', 'Jenis', 'Merek',
        'Rak', 'TipeItem', 'Satuan', 'HargaPokok', 'HargaJual', 'Keterangan',
        'Poin', 'KomisiSales',
    ])."\n".implode(',', [
        'POS-001', 'BAR-POS-001', 'SKU-POS-001', 'Wortel Brastagi',
        'Sayuran', 'Segar Jaya', 'R-01', 'Sayur Basah', 'KG',
        7500, 10000, 'Wortel manis pilihan', 5, 500,
    ])."\n";

    $preview = $this->actingAs($superAdmin, 'sanctum')
        ->post('/api/data/import', [
            'file' => UploadedFile::fake()->createWithContent('pos_import.csv', $csv),
        ])
        ->assertOk()
        ->assertJsonPath('data.valid', true)
        ->assertJsonPath('data.created', 1)
        ->assertJsonPath('data.requires_confirmation', true);

    $this->assertDatabaseMissing('barang', ['KodeItem' => 'POS-001']);

    $this->post('/api/data/import', [
        'file' => UploadedFile::fake()->createWithContent('pos_import.csv', $csv),
        'confirmed' => true,
        'preview_hash' => $preview->json('data.preview_hash'),
    ])
        ->assertOk()
        ->assertJsonPath('data.created', 1)
        ->assertJsonPath('data.requires_confirmation', false);

    $this->assertDatabaseHas('barang', [
        'KodeItem' => 'POS-001',
        'Barcode' => 'BAR-POS-001',
        'SKU' => 'SKU-POS-001',
        'NamaItem' => 'Wortel Brastagi',
        'kategori_id' => $kategori->id,
        'Merek' => 'Segar Jaya',
        'Rak' => 'R-01',
        'TipeItem' => 'Sayur Basah',
        'HargaBeli' => '7500.00',
        'HargaJual' => '10000.00',
        'Keterangan' => 'Wortel manis pilihan',
        'Stok' => 0,
        'Status' => 'tidak_tersedia',
        'Foto' => null,
    ]);
});

test('pos excel import preserves existing stock when updating existing item', function () {
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    $kategori = Kategori::factory()->create(['nama_kategori' => 'buah']);
    Barang::factory()->create([
        'KodeItem' => 'POS-EXISTING',
        'Barcode' => 'BAR-EXISTING',
        'SKU' => 'SKU-EXISTING',
        'NamaItem' => 'Apel Fuji Lama',
        'kategori_id' => $kategori->id,
        'Stok' => 45,
        'Status' => 'tersedia',
        'HargaBeli' => '20000.00',
        'HargaJual' => '25000.00',
    ]);

    $csv = implode(',', [
        'KodeItem', 'Barcode', 'SKU', 'NamaItem', 'Jenis', 'Merek',
        'Rak', 'TipeItem', 'Satuan', 'HargaPokok', 'HargaJual', 'Keterangan',
        'Poin', 'KomisiSales',
    ])."\n".implode(',', [
        'POS-EXISTING', 'BAR-EXISTING', 'SKU-EXISTING', 'Apel Fuji Baru',
        'Buah', 'Impor', 'R-02', 'Buah Segar', 'KG',
        22000, 28000, 'Apel merah renyah', 10, 1000,
    ])."\n";

    $preview = $this->actingAs($superAdmin, 'sanctum')
        ->post('/api/data/import', [
            'file' => UploadedFile::fake()->createWithContent('pos_update.csv', $csv),
        ])
        ->assertOk()
        ->assertJsonPath('data.valid', true)
        ->assertJsonPath('data.created', 0)
        ->assertJsonPath('data.updated', 1)
        ->assertJsonPath('data.stock_changes', []);

    $this->post('/api/data/import', [
        'file' => UploadedFile::fake()->createWithContent('pos_update.csv', $csv),
        'confirmed' => true,
        'preview_hash' => $preview->json('data.preview_hash'),
    ])
        ->assertOk();

    $this->assertDatabaseHas('barang', [
        'KodeItem' => 'POS-EXISTING',
        'NamaItem' => 'Apel Fuji Baru',
        'Stok' => 45, // stock preserved
        'Status' => 'tersedia', // status preserved
        'HargaBeli' => '22000.00',
        'HargaJual' => '28000.00',
    ]);
});
