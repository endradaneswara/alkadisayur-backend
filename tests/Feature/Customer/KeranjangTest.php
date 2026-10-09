<?php

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\User;

test('a customer can add an available product and increase its cart quantity', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $barang = Barang::factory()->create([
        'kategori_id' => Kategori::factory()->create()->id,
        'Stok' => 10,
        'Status' => 'tersedia',
        'HargaJual' => 2500,
    ]);
    $this->actingAs($user, 'sanctum');

    $this->postJson('/api/keranjang/items', [
        'barang_id' => $barang->id,
        'jumlah' => 2,
    ])->assertCreated();

    $this->postJson('/api/keranjang/items', [
        'barang_id' => $barang->id,
        'jumlah' => 3,
    ])->assertCreated();

    $keranjang = Keranjang::where('user_id', $user->id)->firstOrFail();
    $this->assertDatabaseHas('keranjang_item', [
        'keranjang_id' => $keranjang->id,
        'barang_id' => $barang->id,
        'jumlah' => 5,
        'harga' => 2500,
    ]);
});

test('a customer cannot add an unavailable product or more than available stock', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $kategori = Kategori::factory()->create();
    $barang = Barang::factory()->create([
        'kategori_id' => $kategori->id,
        'Stok' => 2,
        'Status' => 'tersedia',
    ]);
    $this->actingAs($user, 'sanctum');

    $this->postJson('/api/keranjang/items', [
        'barang_id' => $barang->id,
        'jumlah' => 3,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('jumlah');

    $barang->update(['Status' => 'tidak_tersedia']);

    $this->postJson('/api/keranjang/items', [
        'barang_id' => $barang->id,
        'jumlah' => 1,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('barang_id');
});

test('guests cannot access the cart', function () {
    $this->getJson('/api/keranjang')->assertUnauthorized();
    $this->postJson('/api/keranjang/items', [])->assertUnauthorized();
});
