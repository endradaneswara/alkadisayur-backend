<?php

use App\Models\User;
use App\Notifications\PasswordResetOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('a user can request a password reset otp by email', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->postJson('/api/forgot-password', ['email' => $user->email])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Jika email terdaftar, kode OTP reset password akan dikirim.');

    Notification::assertSentTo($user, PasswordResetOtp::class);
});

test('a user can reset their password with the emailed otp', function () {
    Notification::fake();
    $user = User::factory()->create(['password' => 'old-password']);
    $token = $user->createToken('existing-device');

    $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();

    $otp = null;
    Notification::assertSentTo($user, PasswordResetOtp::class, function ($notification) use (&$otp) {
        $otp = $notification->otp;

        return true;
    });

    $this->postJson('/api/reset-password', [
        'email' => $user->email,
        'otp' => $otp,
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Password berhasil diperbarui.');

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue()
        ->and($user->tokens()->count())->toBe(0);
});

test('resetting a password rejects malformed or incorrect otp values', function () {
    $user = User::factory()->create();

    $this->postJson('/api/reset-password', [
        'email' => $user->email,
        'otp' => '123',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('otp');

    $this->postJson('/api/reset-password', [
        'email' => $user->email,
        'otp' => '123456',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('otp');
});
