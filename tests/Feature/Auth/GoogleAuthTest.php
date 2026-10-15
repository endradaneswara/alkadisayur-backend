<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;

test('a visitor can be redirected to google authentication', function () {
    $provider = Mockery::mock();
    $provider->shouldReceive('stateless')->once()->andReturnSelf();
    $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.test'));

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $this->get('/api/auth/google')
        ->assertRedirect('https://accounts.google.test');
});

test('a verified google account can sign in and is linked to its user record', function () {
    $googleUser = (new GoogleUser)->map([
        'id' => 'google-user-123',
        'name' => 'Google Customer',
        'email' => 'google@example.com',
        'avatar' => 'https://example.com/avatar.png',
    ])->setRaw(['email_verified' => true]);

    $provider = Mockery::mock();
    $provider->shouldReceive('stateless')->once()->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn($googleUser);

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $response = $this->get('/api/auth/google/callback');
    $response->assertRedirect();

    $redirectUrl = $response->headers->get('Location');

    expect($redirectUrl)
        ->toBe('http://localhost:3000/store/akun/google/callback?status=success');

    $user = User::where('email', 'google@example.com')->firstOrFail();

    expect($user->google_id)->toBe('google-user-123')
        ->and($user->email_verified_at)->not->toBeNull();
    $this->withHeader('Origin', 'http://localhost:3000')
        ->getJson('/api/profile')
        ->assertOk()
        ->assertJsonPath('data.email', 'google@example.com');
});

test('an unverified google account cannot sign in', function () {
    $googleUser = (new GoogleUser)->map([
        'id' => 'google-user-unverified',
        'name' => 'Unverified Customer',
        'email' => 'unverified@example.com',
    ])->setRaw(['email_verified' => false]);

    $provider = Mockery::mock();
    $provider->shouldReceive('stateless')->once()->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn($googleUser);

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $this->getJson('/api/auth/google/callback')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $this->assertDatabaseMissing('users', ['email' => 'unverified@example.com']);
});
