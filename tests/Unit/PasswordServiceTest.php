<?php

use App\Models\User;
use App\Notifications\PasswordResetOtp;
use App\Services\PasswordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it emails a six digit otp and stores only its hash', function () {
    Notification::fake();
    $user = User::factory()->create();

    app(PasswordService::class)->sendResetOtp($user->email);

    $otp = null;
    Notification::assertSentTo($user, PasswordResetOtp::class, function ($notification) use (&$otp) {
        $otp = $notification->otp;

        return true;
    });

    $storedOtp = DB::table('password_reset_tokens')
        ->where('email', $user->email)
        ->first();

    expect($otp)->toMatch('/^\d{6}$/')
        ->and(Hash::check($otp, $storedOtp->token))->toBeTrue()
        ->and($storedOtp->token)->not->toBe($otp);
});

test('it does not reveal whether an email exists when sending an otp', function () {
    Notification::fake();

    app(PasswordService::class)->sendResetOtp('missing@example.com');

    Notification::assertNothingSent();
    $this->assertDatabaseCount('password_reset_tokens', 0);
});

test('it resets the password, deletes the otp and revokes existing api tokens', function () {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);
    $otp = '042681';
    DB::table('password_reset_tokens')->insert([
        'email' => $user->email,
        'token' => Hash::make($otp),
        'created_at' => now(),
    ]);
    $user->createToken('existing-device');

    app(PasswordService::class)->resetPassword([
        'email' => $user->email,
        'otp' => $otp,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $user->refresh();

    expect(Hash::check('new-password', $user->password))->toBeTrue()
        ->and($user->tokens()->count())->toBe(0);
    $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
});

test('it rejects an invalid or expired otp without changing credentials', function () {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);
    DB::table('password_reset_tokens')->insert([
        'email' => $user->email,
        'token' => Hash::make('042681'),
        'created_at' => now()->subMinutes(11),
    ]);
    $user->createToken('existing-device');
    $originalPassword = $user->password;

    expect(fn () => app(PasswordService::class)->resetPassword([
        'email' => $user->email,
        'otp' => '042681',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]))->toThrow(ValidationException::class);

    $user->refresh();

    expect($user->password)->toBe($originalPassword)
        ->and($user->tokens()->count())->toBe(1);
});

test('it rejects an otp after it has already been used', function () {
    $user = User::factory()->create();
    DB::table('password_reset_tokens')->insert([
        'email' => $user->email,
        'token' => Hash::make('042681'),
        'created_at' => now(),
    ]);
    $service = app(PasswordService::class);
    $data = [
        'email' => $user->email,
        'otp' => '042681',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ];

    $service->resetPassword($data);

    expect(fn () => $service->resetPassword($data))
        ->toThrow(ValidationException::class);
});
