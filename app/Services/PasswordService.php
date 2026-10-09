<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PasswordResetOtp;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class PasswordService
{
    private const OTP_EXPIRY_MINUTES = 10;

    public function sendResetOtp(string $email): void
    {
        $user = User::where('email', $email)->first();

        if ($user === null) {
            return;
        }

        $otp = (string) random_int(0, 999999);
        $otp = str_pad($otp, 6, '0', STR_PAD_LEFT);
        $hashedOtp = Hash::make($otp);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => $hashedOtp,
                'created_at' => now(),
            ]
        );

        try {
            $user->notify(new PasswordResetOtp($otp));
        } catch (Throwable $exception) {
            DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->where('token', $hashedOtp)
                ->delete();

            throw $exception;
        }
    }

    public function resetPassword(array $data): void
    {
        DB::transaction(function () use ($data): void {
            $user = User::where('email', $data['email'])
                ->lockForUpdate()
                ->first();
            $resetOtp = DB::table('password_reset_tokens')
                ->where('email', $data['email'])
                ->lockForUpdate()
                ->first();

            $createdAt = $resetOtp?->created_at === null
                ? null
                : Carbon::parse($resetOtp->created_at);

            $isOtpValid = $createdAt !== null
                && $createdAt->greaterThan(now()->subMinutes(self::OTP_EXPIRY_MINUTES))
                && $createdAt->lessThanOrEqualTo(now())
                && Hash::check($data['otp'], $resetOtp->token);

            if ($user === null || ! $isOtpValid) {
                throw ValidationException::withMessages([
                    'otp' => [
                        'Kode OTP tidak valid atau sudah kedaluwarsa.',
                    ],
                ]);
            }

            $user->forceFill([
                'password' => $data['password'],
            ])->save();

            $user->tokens()->delete();

            DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->delete();
        });
    }
}
