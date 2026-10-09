<?php

use App\Models\Lokasi;
use App\Models\User;

test('an authenticated user can list store locations', function () {
    $lokasi = Lokasi::create([
        'nama' => 'Toko Utama',
        'latitude' => -6.20000000,
        'longitude' => 106.80000000,
        'biaya_pengiriman' => 12000,
    ]);

    $this->actingAs(User::factory()->create(['role' => 'superAdmin']), 'sanctum')
        ->getJson('/api/lokasi')
        ->assertOk()
        ->assertJsonPath('data.0.id', $lokasi->id)
        ->assertJsonPath('data.0.nama', 'Toko Utama')
        ->assertJsonPath('meta.per_page', 20);
});

test('a super admin can create a location and set its delivery fee', function () {
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);

    $this->actingAs($superAdmin, 'sanctum')
        ->postJson('/api/lokasi', [
            'nama' => 'Toko Selatan',
            'latitude' => -6.30000000,
            'longitude' => 106.90000000,
            'biaya_pengiriman' => 15000,
        ])
        ->assertCreated()
        ->assertJsonPath('data.nama', 'Toko Selatan');

    $lokasi = Lokasi::where('nama', 'Toko Selatan')->firstOrFail();

    $this->actingAs($superAdmin, 'sanctum')
        ->putJson("/api/lokasi/{$lokasi->id}/biayaPengiriman", [
            'biaya_pengiriman' => 18000,
        ])
        ->assertOk();

    expect((float) $lokasi->fresh()->biaya_pengiriman)->toBe(18000.0);
});

test('a super admin can list, show, update and delete locations', function () {
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    $lokasi = Lokasi::create([
        'nama' => 'Toko Lama',
        'latitude' => -6.41000000,
        'longitude' => 106.91000000,
        'biaya_pengiriman' => 9000,
    ]);
    $this->actingAs($superAdmin, 'sanctum');

    $this->getJson('/api/lokasi')
        ->assertOk()
        ->assertJsonFragment(['id' => $lokasi->id]);

    $this->getJson("/api/lokasi/{$lokasi->id}")
        ->assertOk()
        ->assertJsonPath('data.nama', 'Toko Lama');

    $this->putJson("/api/lokasi/{$lokasi->id}", [
        'nama' => 'Toko Baru',
        'biaya_pengiriman' => 12000,
    ])
        ->assertOk()
        ->assertJsonPath('data.nama', 'Toko Baru')
        ->assertJsonPath('data.biaya_pengiriman', 12000);

    $this->assertDatabaseHas('lokasi', [
        'id' => $lokasi->id,
        'nama' => 'Toko Baru',
        'biaya_pengiriman' => 12000,
    ]);

    $this->deleteJson("/api/lokasi/{$lokasi->id}")
        ->assertOk();

    $this->assertSoftDeleted('lokasi', ['id' => $lokasi->id]);
});

test('a guest cannot list locations', function () {
    $this->getJson('/api/lokasi')->assertUnauthorized();
});
