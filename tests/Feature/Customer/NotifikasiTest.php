<?php

use App\Models\Notifikasi;
use App\Models\User;

test('a customer can read and mark their own notification as read', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $notifikasi = Notifikasi::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/notifikasi')
        ->assertOk()
        ->assertJsonPath('data.0.id', $notifikasi->id)
        ->assertJsonPath('meta.per_page', 20);

    $this->putJson("/api/notifikasi/{$notifikasi->id}/read")
        ->assertOk()
        ->assertJsonPath('data.id', $notifikasi->id);

    expect($notifikasi->fresh()->dibaca_pada)->not->toBeNull();
});

test('a customer cannot read or delete another users notification', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $otherUser = User::factory()->create();
    $notifikasi = Notifikasi::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/notifikasi/{$notifikasi->id}")
        ->assertNotFound();

    $this->deleteJson("/api/notifikasi/{$notifikasi->id}")
        ->assertNotFound();

    $this->assertDatabaseHas('notifikasi', ['id' => $notifikasi->id]);
});

test('a guest cannot access notifications', function () {
    $this->getJson('/api/notifikasi')->assertUnauthorized();
});
