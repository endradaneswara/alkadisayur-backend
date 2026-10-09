<?php

use App\Models\Lokasi;
use App\Models\Pesanan;
use App\Models\User;

test('an admin sees only orders for their assigned store', function () {
    $lokasiA = Lokasi::create([
        'nama' => 'Cabang Utara',
        'latitude' => -6.1,
        'longitude' => 106.7,
        'biaya_pengiriman' => 0,
    ]);
    $lokasiB = Lokasi::create([
        'nama' => 'Cabang Selatan',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'biaya_pengiriman' => 0,
    ]);
    $admin = User::factory()->create([
        'role' => 'admin',
        'lokasi_id' => $lokasiA->id,
    ]);
    $ownOrder = Pesanan::factory()->create(['lokasi_toko_id' => $lokasiA->id]);
    Pesanan::factory()->create(['lokasi_toko_id' => $lokasiB->id]);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/pesanan')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $ownOrder->id);
});

test('an admin can advance an order status but cannot update orders at another store', function () {
    $lokasiA = Lokasi::create([
        'nama' => 'Cabang Barat',
        'latitude' => -6.3,
        'longitude' => 106.9,
        'biaya_pengiriman' => 0,
    ]);
    $lokasiB = Lokasi::create([
        'nama' => 'Cabang Timur',
        'latitude' => -6.4,
        'longitude' => 107.0,
        'biaya_pengiriman' => 0,
    ]);
    $admin = User::factory()->create([
        'role' => 'admin',
        'lokasi_id' => $lokasiA->id,
    ]);
    $ownOrder = Pesanan::factory()->create([
        'lokasi_toko_id' => $lokasiA->id,
        'status' => 'pending',
    ]);
    $otherOrder = Pesanan::factory()->create([
        'lokasi_toko_id' => $lokasiB->id,
        'status' => 'pending',
    ]);
    $this->actingAs($admin, 'sanctum');

    $this->putJson("/api/admin/pesanan/{$ownOrder->id}/status", [
        'status' => 'diproses',
    ])->assertOk()
        ->assertJsonPath('data.status', 'diproses');

    $this->putJson("/api/admin/pesanan/{$otherOrder->id}/status", [
        'status' => 'diproses',
    ])->assertNotFound();
});

test('a customer cannot access admin order routes', function () {
    $this->actingAs(User::factory()->create(['role' => 'customer']), 'sanctum')
        ->getJson('/api/admin/pesanan')
        ->assertForbidden();
});
