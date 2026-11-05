<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Throwable;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
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

    public function login(Request $request): JsonResponse
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

    public function redirectGoogle(): RedirectResponse
    {
        /** @var AbstractProvider $provider */
        $provider = Socialite::driver('google');
        $provider->stateless();

        return $provider->redirect();
    }

    public function callbackGoogle(Request $request): RedirectResponse
    {
        try {
            /** @var AbstractProvider $provider */
            $provider = Socialite::driver('google');
            $provider->stateless();

            $googleUser = $provider->user();
            $email = $googleUser->getEmail();
            $googleId = $googleUser->getId();
            $emailVerified = filter_var(
                $googleUser->user['email_verified'] ?? false,
                FILTER_VALIDATE_BOOLEAN,
            );

            if (! $email || ! $googleId) {
                throw ValidationException::withMessages([
                    'email' => ['Email Google tidak tersedia.'],
                ]);
            }

            if (! $emailVerified) {
                throw ValidationException::withMessages([
                    'email' => ['Email Google belum terverifikasi.'],
                ]);
            }

            $user = DB::transaction(function () use ($googleUser, $email, $googleId): User {
                $byGoogleId = User::where('google_id', $googleId)->lockForUpdate()->first();
                $byEmail = User::where('email', $email)->lockForUpdate()->first();

                if ($byGoogleId && $byEmail && $byGoogleId->isNot($byEmail)) {
                    throw new \RuntimeException('Google identity conflict.');
                }

                $user = $byGoogleId ?? $byEmail;

                if (! $user) {
                    $user = User::create([
                        'name' => $googleUser->getName() ?: $email,
                        'email' => $email,
                        'password' => Str::random(64),
                        'google_id' => $googleId,
                        'avatar' => $googleUser->getAvatar(),
                        'role' => 'customer',
                    ]);
                    $user->forceFill(['email_verified_at' => now()])->save();

                    return $user;
                }

                // Google login must not overwrite an existing profile or role.
                $user->forceFill([
                    'google_id' => $user->google_id ?: $googleId,
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ])->save();

                return $user;
            });

            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            return redirect()->away($this->frontendCallbackUrl('success'));
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::warning('Google authentication failed.', ['exception' => $exception]);

            return $this->googleFailureRedirect();
        }
    }

    private function frontendCallbackUrl(string $status): string
    {
        return rtrim((string) config('services.frontend_url'), '/')
            .'/store/akun/google/callback?status='
            .$status;
    }

    private function googleFailureRedirect(): RedirectResponse
    {
        return redirect()->away($this->frontendCallbackUrl('error'));
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->bearerToken()) {
            $request->user()->currentAccessToken()->delete();
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
