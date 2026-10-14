<?php

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;

test('a super admin can list products with administrative stock and purchase fields', function () {
    $kategori = Kategori::factory()->create();
    $barang = Barang::factory()->create([
        'kategori_id' => $kategori->id,
        'Stok' => 7,
        'HargaBeli' => 1200,
        'HargaJual' => 2500,
    ]);

    $response = $this->actingAs(User::factory()->create(['role' => 'superAdmin']), 'sanctum')
        ->getJson('/api/barang')
        ->assertOk()
        ->assertJsonPath('data.0.id', $barang->id)
        ->assertJsonPath('data.0.HargaJual', '2500.00');

    expect($response->json('data.0'))->toHaveKeys(['Stok', 'HargaBeli', 'KodeItem']);
});

test('a super admin can create a product and invalid stock is rejected', function () {
    $kategori = Kategori::factory()->create();
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    $payload = [
        'kategori_id' => $kategori->id,
        'KodeItem' => 'TEST-001',
        'Barcode' => 'BAR-TEST-001',
        'SKU' => 'SKU-TEST-001',
        'NamaItem' => 'Bayam Segar',
        'Stok' => 8,
        'HargaBeli' => 1000,
        'HargaJual' => 2000,
        'Status' => 'tersedia',
    ];

    $this->actingAs($superAdmin, 'sanctum')
        ->postJson('/api/barang', $payload)
        ->assertCreated()
        ->assertJsonPath('data.KodeItem', 'TEST-001');

    $this->assertDatabaseHas('barang', [
        'KodeItem' => 'TEST-001',
        'Stok' => 8,
    ]);

    $payload['KodeItem'] = 'TEST-002';
    $payload['Barcode'] = 'BAR-TEST-002';
    $payload['SKU'] = 'SKU-TEST-002';
    $payload['Stok'] = -1;

    $this->postJson('/api/barang', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('Stok');
});

test('a super admin can list, show, update and delete products', function () {
    $kategori = Kategori::factory()->create();
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    $barang = Barang::factory()->create([
        'kategori_id' => $kategori->id,
        'KodeItem' => 'CRUD-ITEM-1',
        'Barcode' => 'CRUD-BARCODE-1',
        'SKU' => 'CRUD-SKU-1',
        'NamaItem' => 'Bayam Lama',
    ]);
    $this->actingAs($superAdmin, 'sanctum');

    $this->getJson('/api/barang')
        ->assertOk()
        ->assertJsonFragment(['id' => $barang->id]);

    $this->getJson("/api/barang/{$barang->id}")
        ->assertOk()
        ->assertJsonPath('data.KodeItem', 'CRUD-ITEM-1');

    $this->putJson("/api/barang/{$barang->id}", [
        'NamaItem' => 'Bayam Baru',
        'Stok' => 15,
    ])
        ->assertOk()
        ->assertJsonPath('data.NamaItem', 'Bayam Baru')
        ->assertJsonPath('data.Stok', 15);

    $this->assertDatabaseHas('barang', [
        'id' => $barang->id,
        'NamaItem' => 'Bayam Baru',
        'Stok' => 15,
    ]);

    $this->deleteJson("/api/barang/{$barang->id}")
        ->assertOk();

    $this->assertDatabaseMissing('barang', ['id' => $barang->id]);
});

test('a guest can browse the public product catalog without administrative data', function () {
    $kategori = Kategori::factory()->create();
    $barang = Barang::factory()->create([
        'kategori_id' => $kategori->id,
        'KodeItem' => 'PUBLIC-ITEM-1',
        'Stok' => 7,
        'HargaBeli' => 1200,
        'HargaJual' => 2500,
    ]);

    $this->getJson('/api/barang')
        ->assertOk()
        ->assertJsonPath('data.0.id', $barang->id)
        ->assertJsonPath('data.0.NamaItem', $barang->NamaItem)
        ->assertJsonPath('data.0.HargaJual', '2500.00')
        ->assertJsonMissingPath('data.0.Stok')
        ->assertJsonMissingPath('data.0.HargaBeli')
        ->assertJsonMissingPath('data.0.KodeItem');

    $this->getJson("/api/barang/{$barang->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $barang->id)
        ->assertJsonMissingPath('data.Stok')
        ->assertJsonMissingPath('data.HargaBeli');
});

test('the API accepts cross-origin requests from the local frontend', function () {
    $this->call('OPTIONS', '/api/barang', [], [], [], [
        'HTTP_ORIGIN' => 'http://localhost:3000',
        'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'authorization,content-type',
    ])
        ->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertHeader('Access-Control-Allow-Methods', 'GET');
});
