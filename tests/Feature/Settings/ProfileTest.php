<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

test('an authenticated user can view their profile without exposing credentials', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/profile')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonMissingPath('data.password')
        ->assertJsonMissingPath('data.google_id');
});

test('a user can update profile details without losing email verification when the email is unchanged', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'profile@example.com',
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', [
            'name' => 'Updated Name',
            'email' => $user->email,
            'no_hp' => '081234567890',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.no_hp', '081234567890');

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('a user can update their profile without changing their email', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'optional-email@example.com',
    ]);
    $verifiedAt = $user->email_verified_at;

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', [
            'name' => 'Updated Without Email',
            'no_hp' => '081234567890',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Without Email')
        ->assertJsonPath('data.email', 'optional-email@example.com');

    expect($user->fresh()->email_verified_at->equalTo($verifiedAt))->toBeTrue();
});

test('a user can update only their name without submitting other profile fields', function () {
    $user = User::factory()->create([
        'name' => 'Before Name',
        'email' => 'name-only@example.com',
    ]);
    $verifiedAt = $user->email_verified_at;

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', ['name' => 'After Name'])
        ->assertOk()
        ->assertJsonPath('data.name', 'After Name')
        ->assertJsonPath('data.email', 'name-only@example.com');

    $user->refresh();

    expect($user->name)->toBe('After Name')
        ->and($user->email_verified_at->equalTo($verifiedAt))->toBeTrue();
});

test('changing an email resets verification and sends a new verification notification', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'old@example.com']);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', [
            'name' => $user->name,
            'email' => 'new@example.com',
        ])
        ->assertOk()
        ->assertJsonPath('data.email', 'new@example.com')
        ->assertJsonPath('data.email_verified_at', null);

    expect($user->fresh()->email_verified_at)->toBeNull();
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('a guest cannot view or update a profile', function () {
    $this->getJson('/api/profile')->assertUnauthorized();
    $this->putJson('/api/profile', [])->assertUnauthorized();
});
