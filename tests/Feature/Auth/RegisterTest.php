<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

test('a visitor can register a customer account', function () {
    Notification::fake();

    $response = $this->postJson('/api/register', [
        'name' => 'New Customer',
        'email' => 'customer@example.com',
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.name', 'New Customer')
        ->assertJsonPath('data.user.email', 'customer@example.com')
        ->assertJsonPath('data.user.role', 'customer')
        ->assertJsonStructure(['data' => ['user' => ['id'], 'token']]);

    $user = User::where('email', 'customer@example.com')->firstOrFail();

    expect($user->email_verified_at)->toBeNull();
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('registration requires a valid unique email and matching password confirmation', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/register', [
        'name' => 'New Customer',
        'email' => 'taken@example.com',
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'different-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

test('a browser registration uses the session cookie and does not return an api token', function () {
    Notification::fake();

    $response = $this->withHeader('Origin', 'http://localhost:3000')
        ->postJson('/api/register', [
            'name' => 'Cookie Customer',
            'email' => 'cookie-customer@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.user.email', 'cookie-customer@example.com')
        ->assertJsonMissingPath('data.token')
        ->assertCookie(config('session.cookie'));

    $this->withHeader('Origin', 'http://localhost:3000')
        ->getJson('/api/profile')
        ->assertOk()
        ->assertJsonPath('data.email', 'cookie-customer@example.com');
});
