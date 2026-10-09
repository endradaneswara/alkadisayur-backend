<?php

use App\Models\User;

test('a super admin can list users without exposing password hashes', function () {
    User::factory()->create(['name' => 'Listed Customer']);
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);

    $response = $this->actingAs($superAdmin, 'sanctum')
        ->getJson('/api/users')
        ->assertOk()
        ->assertJsonPath('meta.per_page', 20)
        ->assertJsonStructure(['data', 'links', 'meta']);

    expect($response->json('data.0'))->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});

test('a super admin can change a users role and invalid roles are rejected', function () {
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    $customer = User::factory()->create(['role' => 'customer']);
    $this->actingAs($superAdmin, 'sanctum');

    $this->putJson("/api/users/{$customer->id}/role", ['role' => 'admin'])
        ->assertOk()
        ->assertJsonPath('data.role', 'admin');

    expect($customer->fresh()->role)->toBe('admin');

    $this->putJson("/api/users/{$customer->id}/role", ['role' => 'root'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('role');
});

test('an admin cannot manage user roles', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']), 'sanctum')
        ->getJson('/api/users')
        ->assertForbidden();
});
