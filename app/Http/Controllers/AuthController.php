<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\TransientToken;
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

        return $this->authenticatedResponse($request, $user, 'Registrasi akun berhasil.');
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

        return $this->authenticatedResponse($request, $user, 'Login berhasil.');
    }

    public function redirectGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callbackGoogle(Request $request)
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

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        $frontendCallback = rtrim(
            (string) config('services.frontend_url'),
            '/'
        ).'/store/akun/google/callback';

        return redirect()->away($frontendCallback.'?status=success');
    }

    public function logout(Request $request)
    {
        $accessToken = $request->user()->currentAccessToken();

        if ($accessToken && ! $accessToken instanceof TransientToken) {
            $accessToken->delete();
        }

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->success(null, 'Logout berhasil.');
    }

    private function authenticatedResponse(
        Request $request,
        User $user,
        string $message,
    ): JsonResponse {
        if ($request->hasSession()) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();
            $user->loadMissing('lokasi');
            $userData = array_merge(
                $user->only([
                    'id',
                    'name',
                    'email',
                    'role',
                    'no_hp',
                    'avatar',
                    'email_verified_at',
                    'lokasi_id',
                ]),
                ['lokasi' => $user->lokasi]
            );

            $data = [
                'user' => $userData,
            ];
        } else {
            $user->loadMissing('lokasi');
            $userData = array_merge(
                $user->only([
                    'id',
                    'name',
                    'email',
                    'role',
                    'no_hp',
                    'avatar',
                    'email_verified_at',
                    'lokasi_id',
                ]),
                ['lokasi' => $user->lokasi]
            );

            $data = [
                'user' => $userData,
                'token' => $user->createToken('api-token')->plainTextToken,
            ];
        }

        return $this->success($data, $message);
    }
}
