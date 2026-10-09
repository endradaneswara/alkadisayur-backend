<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('an authenticated user can change their password with their current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile/password', [
            'current_password' => 'current-password',
            'new_password' => 'new-password-123',
            'new_password_confirmation' => 'new-password-123',
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

test('a user cannot change their password with an incorrect current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);
    $oldPasswordHash = $user->password;

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile/password', [
            'current_password' => 'incorrect-password',
            'new_password' => 'new-password-123',
            'new_password_confirmation' => 'new-password-123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');

    expect($user->fresh()->password)->toBe($oldPasswordHash);
});

test('a guest cannot change a password', function () {
    $this->putJson('/api/profile/password', [])->assertUnauthorized();
});
