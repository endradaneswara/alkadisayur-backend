<?php

use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\User;

test('a customer can create one payment for their order', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $pesanan = Pesanan::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total_harga' => 20000,
        'biaya_pengiriman' => 5000,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/pembayaran', [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'QRIS',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.jumlah', '25000.00');

    $this->assertDatabaseHas('pembayaran', [
        'pesanan_id' => $pesanan->id,
        'metode_pembayaran' => 'QRIS',
        'jumlah' => 25000,
    ]);

    $this->postJson('/api/pembayaran', [
        'pesanan_id' => $pesanan->id,
        'metode_pembayaran' => 'Transfer',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('pesanan_id');
});

test('a customer cannot read another customers payment', function () {
    $owner = User::factory()->create(['role' => 'customer']);
    $otherUser = User::factory()->create();
    $pesanan = Pesanan::factory()->create(['user_id' => $otherUser->id]);
    $pembayaran = Pembayaran::factory()->create(['pesanan_id' => $pesanan->id]);

    $this->actingAs($owner, 'sanctum')
        ->getJson("/api/pembayaran/{$pembayaran->id}")
        ->assertNotFound();
});

test('a guest cannot create a payment', function () {
    $this->postJson('/api/pembayaran', [])->assertUnauthorized();
});
