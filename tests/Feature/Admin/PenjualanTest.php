<?php

use App\Models\Lokasi;
use App\Models\Pesanan;
use App\Models\User;

test('an admin sees completed sales and summary only for their store', function () {
    $lokasiA = Lokasi::create([
        'nama' => 'Sales Cabang A',
        'latitude' => -6.11,
        'longitude' => 106.71,
        'biaya_pengiriman' => 5000,
    ]);
    $lokasiB = Lokasi::create([
        'nama' => 'Sales Cabang B',
        'latitude' => -6.22,
        'longitude' => 106.82,
        'biaya_pengiriman' => 6000,
    ]);
    $admin = User::factory()->create([
        'role' => 'admin',
        'lokasi_id' => $lokasiA->id,
    ]);
    $sale = Pesanan::factory()->create([
        'lokasi_toko_id' => $lokasiA->id,
        'status' => 'selesai',
        'total_harga' => 30000,
        'biaya_pengiriman' => 5000,
    ]);
    Pesanan::factory()->create([
        'lokasi_toko_id' => $lokasiA->id,
        'status' => 'pending',
    ]);
    Pesanan::factory()->create([
        'lokasi_toko_id' => $lokasiB->id,
        'status' => 'selesai',
    ]);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/penjualan')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $sale->id);

    $this->getJson('/api/admin/penjualan/summary')
        ->assertOk()
        ->assertJsonPath('data.total_transaksi', 1)
        ->assertJsonPath('data.total_pendapatan', 35000);
});

test('sales date filters reject an end date before the start date', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/penjualan?tanggal_mulai=2026-10-10&tanggal_selesai=2026-10-09')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('tanggal_selesai');
});

test('a customer cannot access sales reports', function () {
    $this->actingAs(User::factory()->create(['role' => 'customer']), 'sanctum')
        ->getJson('/api/admin/penjualan')
        ->assertForbidden();
});
