<?php

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Lokasi;
use App\Models\User;

test('protected api endpoints reject guests', function () {
    $this->getJson('/api/profile')->assertUnauthorized();
    $this->getJson('/api/barang')->assertUnauthorized();
    $this->getJson('/api/pesanan')->assertUnauthorized();
});

test('customer cannot access admin or super admin endpoints', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer, 'sanctum')
        ->getJson('/api/admin/pesanan')
        ->assertForbidden();

    $this->getJson('/api/dashboard')->assertForbidden();
    $this->getJson('/api/data/export')->assertForbidden();
});

test('admin can access admin routes but cannot access super admin routes', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/notifikasi')
        ->assertOk();

    $this->getJson('/api/dashboard')->assertForbidden();
    $this->getJson('/api/users')->assertForbidden();
});

test('super admin can access customer admin and super admin api read routes', function () {
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    $kategori = Kategori::factory()->create();
    $barang = Barang::factory()->create(['kategori_id' => $kategori->id]);
    $lokasi = Lokasi::create([
        'nama' => 'Authorization Test Store',
        'latitude' => -6.25,
        'longitude' => 106.85,
        'biaya_pengiriman' => 0,
    ]);

    $this->actingAs($superAdmin, 'sanctum');

    $urls = [
        '/api/profile',
        '/api/kategori',
        "/api/kategori/{$kategori->id}",
        "/api/kategori/{$kategori->id}/barang",
        '/api/barang',
        "/api/barang/{$barang->id}",
        '/api/lokasi',
        "/api/lokasi/{$lokasi->id}",
        '/api/alamat',
        '/api/keranjang',
        '/api/pesanan',
        '/api/pembayaran',
        '/api/ulasan',
        '/api/notifikasi',
        '/api/admin/pesanan',
        '/api/admin/penjualan',
        '/api/admin/penjualan/summary',
        '/api/admin/notifikasi',
        '/api/dashboard',
        '/api/users',
        "/api/users/{$superAdmin->id}",
        '/api/penjualan',
        '/api/penjualan/summary',
    ];

    foreach ($urls as $url) {
        $this->getJson($url)->assertOk("Super admin could not access {$url}");
    }
});
