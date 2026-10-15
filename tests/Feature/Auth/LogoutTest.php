<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

test('a browser user can log out and invalidate their session', function () {
    User::factory()->create([
        'email' => 'cookie-logout@example.com',
        'password' => bcrypt('correct-password'),
    ]);

    $this->withHeader('Origin', 'http://localhost:3000')
        ->postJson('/api/login', [
            'login' => 'cookie-logout@example.com',
            'password' => 'correct-password',
        ])
        ->assertOk()
        ->assertJsonMissingPath('data.token');

    $this->withHeader('Origin', 'http://localhost:3000')
        ->postJson('/api/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logout berhasil.');

    Auth::forgetGuards();

    $this->withHeader('Origin', 'http://localhost:3000')
        ->getJson('/api/profile')
        ->assertUnauthorized();
});
