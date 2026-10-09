<?php

use App\Models\Lokasi;
use App\Models\User;

test('a super admin can update a stores delivery fee', function () {
    $lokasi = Lokasi::create([
        'nama' => 'Lokasi Ongkir',
        'latitude' => -6.5,
        'longitude' => 107.1,
        'biaya_pengiriman' => 5000,
    ]);
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);

    $this->actingAs($superAdmin, 'sanctum')
        ->putJson("/api/lokasi/{$lokasi->id}/biayaPengiriman", [
            'biaya_pengiriman' => 15000,
        ])
        ->assertOk()
        ->assertJsonPath('data.biaya_pengiriman', 15000);

    expect((float) $lokasi->fresh()->biaya_pengiriman)->toBe(15000.0);
});

test('negative delivery fees are rejected', function () {
    $lokasi = Lokasi::create([
        'nama' => 'Lokasi Ongkir Validasi',
        'latitude' => -6.6,
        'longitude' => 107.2,
        'biaya_pengiriman' => 5000,
    ]);
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);

    $this->actingAs($superAdmin, 'sanctum')
        ->putJson("/api/lokasi/{$lokasi->id}/biayaPengiriman", [
            'biaya_pengiriman' => -1,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('biaya_pengiriman');
});

test('an admin cannot change delivery fees', function () {
    $lokasi = Lokasi::create([
        'nama' => 'Lokasi Ongkir Admin',
        'latitude' => -6.7,
        'longitude' => 107.3,
        'biaya_pengiriman' => 5000,
    ]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin, 'sanctum')
        ->putJson("/api/lokasi/{$lokasi->id}/biayaPengiriman", [
            'biaya_pengiriman' => 15000,
        ])
        ->assertForbidden();
});
