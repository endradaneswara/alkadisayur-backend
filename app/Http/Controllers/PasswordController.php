<?php

namespace App\Http\Controllers;

use App\Http\Requests\LupaPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Services\PasswordService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PasswordService $passwordService
    ) {}

    public function forgot(LupaPasswordRequest $request)
    {
        $this->passwordService->sendResetOtp(
            $request->email
        );

        return $this->success(
            null,
            'Jika email terdaftar, kode OTP reset password akan dikirim.'
        );
    }

    public function reset(ResetPasswordRequest $request)
    {
        $this->passwordService->resetPassword(
            $request->validated()
        );

        return $this->success(
            null,
            'Password berhasil diperbarui.'
        );
    }

    public function update(UpdatePasswordRequest $request)
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
