<?php

namespace App\Http\Controllers;

use App\Http\Requests\LupaPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\VerifyResetOtpRequest;
use App\Services\PasswordService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PasswordService $passwordService
    ) {}

    public function forgot(LupaPasswordRequest $request): JsonResponse
    {
        $this->passwordService->sendResetOtp(
            $request->email
        );

        return $this->success(
            null,
            'Jika email terdaftar, kode OTP reset password akan dikirim.'
        );
    }

    public function verifyOtp(VerifyResetOtpRequest $request): JsonResponse
    {
        $this->passwordService->verifyResetOtp(
            $request->email,
            $request->otp
        );

        return $this->success(
            null,
            'Kode OTP valid.'
        );
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $this->passwordService->resetPassword(
            $request->validated()
        );

        return $this->success(
            null,
            'Password berhasil diperbarui.'
        );
    }

    public function update(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return $this->success(
            null,
            'Password berhasil diperbarui.'
        );
    }
}
