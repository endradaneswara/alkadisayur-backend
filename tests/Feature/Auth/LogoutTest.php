<?php

use App\Models\User;

test('an authenticated user can log out and revoke their current api token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device');

    $response = $this->withToken($token->plainTextToken)
        ->postJson('/api/logout');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Logout berhasil.');

    expect($user->tokens()->count())->toBe(0);
});

test('a guest cannot log out', function () {
    $this->postJson('/api/logout')->assertUnauthorized();
});
