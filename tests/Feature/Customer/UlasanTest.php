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
