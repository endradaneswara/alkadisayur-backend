<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'customer',
        ]);
        $user->sendEmailVerificationNotification();

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => $user->only([
                'id',
                'name',
                'email',
                'role',
                'no_hp',
                'avatar',
                'email_verified_at',
            ]),
            'token' => $token,
        ], 'Registrasi akun berhasil.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['login'])
            ->orWhere('name', $credentials['login'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Nama, email, atau password salah.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => $user->only([
                'id',
                'name',
                'email',
                'role',
                'no_hp',
                'avatar',
                'email_verified_at',
            ]),
            'token' => $token,
        ], 'Login berhasil.');
    }

    public function redirectGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callbackGoogle()
    {
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->user();

        if (! $googleUser->getEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Email Google tidak tersedia.'],
            ]);
        }

        if (! ($googleUser->user['email_verified'] ?? false)) {
            throw ValidationException::withMessages([
                'email' => ['Email Google belum terverifikasi.'],
            ]);
        }

        $user = User::where(
            'google_id',
            $googleUser->getId()
        )->first();

        if (! $user) {
            $user = User::where(
                'email',
                $googleUser->getEmail()
            )->first();
        }

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'name' => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar(),
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => Str::random(32),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'role' => 'customer',
            ]);
        }

        if ($googleUser->user['email_verified'] ?? false) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => $user->only([
                'id',
                'name',
                'email',
                'role',
                'no_hp',
                'avatar',
                'email_verified_at',
            ]),
            'token' => $token,
        ], 'Login dengan Google berhasil.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout berhasil.');
    }
}
