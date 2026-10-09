<?php

use App\Models\Barang;
use App\Models\Kategori;
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

test('a regular admin cannot access the super admin dashboard', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']), 'sanctum')
        ->getJson('/api/dashboard')
        ->assertForbidden();
});
