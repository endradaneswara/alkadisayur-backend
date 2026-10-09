<?php

use App\Models\Alamat;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\Lokasi;
use App\Models\Pesanan;
use App\Models\User;

function prepareCustomerOrder($test, User $user, int $stock = 10): array
{
    $kategori = Kategori::factory()->create();
    $barang = Barang::factory()->create([
        'kategori_id' => $kategori->id,
        'Stok' => $stock,
        'HargaJual' => 2500,
        'Status' => 'tersedia',
    ]);
    $alamat = Alamat::factory()->create(['user_id' => $user->id]);
    $lokasi = Lokasi::create([
        'nama' => fake()->unique()->city(),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'biaya_pengiriman' => 10000,
    ]);
    $test->actingAs($user, 'sanctum')
        ->postJson('/api/keranjang/items', [
            'barang_id' => $barang->id,
            'jumlah' => 2,
        ])->assertCreated();

    return [$barang, $alamat, $lokasi];
}

test('a customer can place an order and stock is deducted from the product', function () {
    $user = User::factory()->create(['role' => 'customer']);
    [$barang, $alamat, $lokasi] = prepareCustomerOrder($this, $user);

    $response = $this->postJson('/api/pesanan', [
        'metode' => 'pesan antar',
        'alamat_id' => $alamat->id,
        'lokasi_toko_id' => $lokasi->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.metode', 'pesan_antar')
        ->assertJsonPath('data.total_harga', '5000.00')
        ->assertJsonPath('data.items.0.nama_barang', $barang->NamaItem);

    expect((int) $barang->fresh()->Stok)->toBe(8)
        ->and(Keranjang::where('user_id', $user->id)->firstOrFail()->items()->count())->toBe(0);
});

test('a customer can cancel a pending order and stock is restored only once', function () {
    $user = User::factory()->create(['role' => 'customer']);
    [$barang, $alamat, $lokasi] = prepareCustomerOrder($this, $user);

    $orderResponse = $this->postJson('/api/pesanan', [
        'metode' => 'ambil_ditoko',
        'lokasi_toko_id' => $lokasi->id,
    ])->assertCreated();
    $orderId = $orderResponse->json('data.id');

    $this->deleteJson("/api/pesanan/{$orderId}")
        ->assertOk()
        ->assertJsonPath('data.status', 'dibatalkan');

    expect((int) $barang->fresh()->Stok)->toBe(10);

    $this->deleteJson("/api/pesanan/{$orderId}")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');

    expect((int) $barang->fresh()->Stok)->toBe(10);
});

test('a customer cannot view another customers order', function () {
    $owner = User::factory()->create(['role' => 'customer']);
    $otherUser = User::factory()->create();
    $pesanan = Pesanan::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($owner, 'sanctum')
        ->getJson("/api/pesanan/{$pesanan->id}")
        ->assertNotFound();
});
