<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => $user->only(['id', 'name', 'email']),
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

        $user = User::updateOrCreate(
            [
                'google_id' => $googleUser->getId(),
            ],
            [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
            ]
        );

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => $user->only([
                'id',
                'name',
                'email',
                'avatar',
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