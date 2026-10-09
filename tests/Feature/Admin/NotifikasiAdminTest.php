<?php

use App\Models\Notifikasi;
use App\Models\User;

test('an admin can list and mark their notifications as read', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $notifikasi = Notifikasi::factory()->create(['user_id' => $admin->id]);
    Notifikasi::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/notifikasi')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $notifikasi->id);

    $this->putJson('/api/admin/notifikasi/read-all')->assertOk();

    expect($notifikasi->fresh()->dibaca_pada)->not->toBeNull();
});

test('a customer cannot access admin notifications', function () {
    $this->actingAs(User::factory()->create(['role' => 'customer']), 'sanctum')
        ->getJson('/api/admin/notifikasi')
        ->assertForbidden();
});
