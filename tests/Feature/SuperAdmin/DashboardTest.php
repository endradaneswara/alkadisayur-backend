<?php

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Lokasi;
use App\Models\Pesanan;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('a super admin can view accurate dashboard totals', function () {
    $kategori = Kategori::factory()->create();
    Barang::factory()->create([
        'kategori_id' => $kategori->id,
        'Stok' => 2,
        'Status' => 'tidak_tersedia',
    ]);
    Pesanan::factory()->create([
        'status' => 'selesai',
        'total_harga' => 25000,
        'biaya_pengiriman' => 5000,
    ]);
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);

    $this->actingAs($superAdmin, 'sanctum')
        ->getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.ringkasan.total_produk', 1)
        ->assertJsonPath('data.ringkasan.total_kategori', 1)
        ->assertJsonPath('data.pesanan.selesai', 1)
        ->assertJsonPath('data.ringkasan.total_penjualan', 30000)
        ->assertJsonPath('data.stok.stok_menipis', 1)
        ->assertJsonPath('data.stok.produk_tidak_tersedia', 1);
});

test('dashboard returns a daily sales chart matching the requested range', function () {
    Pesanan::factory()->create([
        'status' => 'selesai',
        'total_harga' => 20000,
        'biaya_pengiriman' => 5000,
        'tanggal_pemesanan' => now(),
    ]);
    Pesanan::factory()->create([
        'status' => 'selesai',
        'total_harga' => 10000,
        'biaya_pengiriman' => 0,
        'tanggal_pemesanan' => now()->subDays(3),
    ]);
    Pesanan::factory()->create([
        'status' => 'diproses',
        'total_harga' => 90000,
        'biaya_pengiriman' => 5000,
        'tanggal_pemesanan' => now(),
    ]);
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);

    $response = $this->actingAs($superAdmin, 'sanctum')
        ->getJson('/api/dashboard?rentang=7h')
        ->assertOk();

    $grafik = $response->json('data.grafik_penjualan');
    expect($grafik)->toHaveCount(7)
        ->and($grafik[6]['tanggal'])->toBe(now()->format('Y-m-d'))
        ->and((float) $grafik[6]['total_penjualan'])->toBe(25000.0)
        ->and($grafik[6]['total_pesanan'])->toBe(1)
        ->and((float) $grafik[3]['total_penjualan'])->toBe(10000.0)
        ->and((float) $grafik[0]['total_penjualan'])->toBe(0.0);

    $this->getJson('/api/dashboard?rentang=1h')
        ->assertOk()
        ->assertJsonCount(1, 'data.grafik_penjualan')
        ->assertJsonPath('data.grafik_penjualan.0.total_penjualan', 25000);

    $this->getJson('/api/dashboard?rentang=30h')
        ->assertOk()
        ->assertJsonCount(30, 'data.grafik_penjualan');
});

test('dashboard rejects an unsupported chart range', function () {
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);

    $this->actingAs($superAdmin, 'sanctum')
        ->getJson('/api/dashboard?rentang=90d')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('rentang');
});

test('dashboard returns the five most recent orders with customer data', function () {
    for ($i = 1; $i <= 6; $i++) {
        Pesanan::factory()->create([
            'status' => 'pending',
            'tanggal_pemesanan' => now()->subDays(10 - $i),
        ]);
    }
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);

    $pesananTerbaru = $this->actingAs($superAdmin, 'sanctum')
        ->getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonCount(5, 'data.pesanan_terbaru')
        ->json('data.pesanan_terbaru');

    expect(collect($pesananTerbaru)->pluck('nomor_order')->all())
        ->each->toBeString();
});

test('an admin sees dashboard totals scoped to their store location', function () {
    Barang::factory()->create([
        'kategori_id' => Kategori::factory()->create()->id,
    ]);
    $lokasiA = Lokasi::create([
        'nama' => 'Dashboard Cabang A',
        'latitude' => -6.11,
        'longitude' => 106.71,
        'biaya_pengiriman' => 5000,
    ]);
    $lokasiB = Lokasi::create([
        'nama' => 'Dashboard Cabang B',
        'latitude' => -6.22,
        'longitude' => 106.82,
        'biaya_pengiriman' => 6000,
    ]);
    Pesanan::factory()->create([
        'lokasi_toko_id' => $lokasiA->id,
        'status' => 'selesai',
        'total_harga' => 25000,
        'biaya_pengiriman' => 5000,
    ]);
    Pesanan::factory()->create([
        'lokasi_toko_id' => $lokasiA->id,
        'status' => 'pending',
    ]);
    Pesanan::factory()->create([
        'lokasi_toko_id' => $lokasiB->id,
        'status' => 'selesai',
        'total_harga' => 100000,
        'biaya_pengiriman' => 6000,
    ]);

    $this->actingAs(
        User::factory()->create(['role' => 'admin', 'lokasi_id' => $lokasiA->id]),
        'sanctum'
    )
        ->getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonPath('data.ringkasan.total_pesanan', 2)
        ->assertJsonPath('data.ringkasan.total_penjualan', 30000)
        ->assertJsonPath('data.pesanan.selesai', 1)
        ->assertJsonPath('data.pesanan.pending', 1)
        ->assertJsonCount(7, 'data.grafik_penjualan')
        ->assertJsonCount(2, 'data.pesanan_terbaru');
});

test('a regular admin without location sees an empty dashboard', function () {
    Barang::factory()->create([
        'kategori_id' => Kategori::factory()->create()->id,
    ]);

    $this->actingAs(User::factory()->create(['role' => 'admin']), 'sanctum')
        ->getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonPath('data.ringkasan.total_pesanan', 0)
        ->assertJsonPath('data.ringkasan.total_penjualan', 0)
        ->assertJsonCount(0, 'data.pesanan_terbaru');
});

test('a customer cannot access the admin dashboard', function () {
    $this->actingAs(User::factory()->create(['role' => 'customer']), 'sanctum')
        ->getJson('/api/dashboard')
        ->assertForbidden();
});
