<?php

use App\Models\Alamat;
use App\Models\User;

test('a customer can create and list their own addresses', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $payload = [
        'nama_alamat' => 'Rumah',
        'nama_penerima' => 'Penerima',
        'no_hp' => '081234567890',
        'alamat_lengkap' => 'Jalan Sayur Nomor 1',
    ];

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/alamat', $payload)
        ->assertCreated()
        ->assertJsonPath('data.nama_alamat', 'Rumah');

    $this->assertDatabaseHas('alamat', [
        'user_id' => $user->id,
        'nama_alamat' => 'Rumah',
    ]);

    Alamat::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->getJson('/api/alamat')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('a customer cannot view or delete another customers address', function () {
    $owner = User::factory()->create(['role' => 'customer']);
    $otherUser = User::factory()->create();
    $alamat = Alamat::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($owner, 'sanctum')
        ->getJson("/api/alamat/{$alamat->id}")
        ->assertNotFound();

    $this->deleteJson("/api/alamat/{$alamat->id}")
        ->assertNotFound();

    $this->assertDatabaseHas('alamat', ['id' => $alamat->id]);
});

test('address input is validated', function () {
    $this->actingAs(User::factory()->create(['role' => 'customer']), 'sanctum')
        ->postJson('/api/alamat', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['nama_alamat', 'nama_penerima', 'no_hp', 'alamat_lengkap']);
});

test('a guest cannot access addresses', function () {
    $this->getJson('/api/alamat')->assertUnauthorized();
});
