<?php

use App\Models\Ulasan;
use App\Models\User;

test('a customer can submit one review', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/ulasan', [
            'rating' => 5,
            'komentar' => 'Layanan sangat baik.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.rating', 5);

    $this->assertDatabaseHas('ulasan', [
        'user_id' => $user->id,
        'rating' => 5,
    ]);

    $this->postJson('/api/ulasan', ['rating' => 4])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('ulasan');
});

test('a customer cannot change or delete another customers review', function () {
    $owner = User::factory()->create(['role' => 'customer']);
    $otherUser = User::factory()->create();
    $ulasan = Ulasan::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($owner, 'sanctum')
        ->putJson("/api/ulasan/{$ulasan->id}", ['rating' => 1])
        ->assertNotFound();

    $this->deleteJson("/api/ulasan/{$ulasan->id}")
        ->assertNotFound();

    expect($ulasan->fresh()->rating)->toBe($ulasan->rating);
});

test('a guest cannot submit a review', function () {
    $this->postJson('/api/ulasan', ['rating' => 5])->assertUnauthorized();
});

test('a super admin can view all reviews and filter them', function () {
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    $user1 = User::factory()->create(['name' => 'Budi Santoso']);
    $user2 = User::factory()->create(['name' => 'Siti Rahma']);

    Ulasan::factory()->create([
        'user_id' => $user1->id,
        'rating' => 5,
        'komentar' => 'Sayur sangat segar dan berkualitas.',
    ]);
    Ulasan::factory()->create([
        'user_id' => $user2->id,
        'rating' => 3,
        'komentar' => 'Pengiriman agak terlambat.',
    ]);

    $this->actingAs($superAdmin, 'sanctum')
        ->getJson('/api/ulasan')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->getJson('/api/ulasan?rating=5')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.rating', 5);

    $this->getJson('/api/ulasan?search=Budi')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.user.name', 'Budi Santoso');
});

test('a super admin can delete any review', function () {
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    $user = User::factory()->create();
    $ulasan = Ulasan::factory()->create(['user_id' => $user->id]);

    $this->actingAs($superAdmin, 'sanctum')
        ->deleteJson("/api/ulasan/{$ulasan->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Ulasan berhasil dihapus.');

    $this->assertDatabaseMissing('ulasan', [
        'id' => $ulasan->id,
    ]);
});
