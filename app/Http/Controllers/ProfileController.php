<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    public function show(Request $request)
    {
        $user = $request->user();

        return $this->success(
            $user->only([
                'id',
                'name',
                'email',
                'no_hp',
                'avatar',
                'role',
                'email_verified_at',
            ]),
            'Data profile berhasil diambil.'
        );
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $emailChanged = isset($data['email']) && $user->email !== $data['email'];

        if ($emailChanged) {
            $data['email_verified_at'] = null;
        }

        $user->forceFill($data)->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return $this->success(
            $user->only([
                'id',
                'name',
                'email',
                'no_hp',
                'avatar',
                'role',
                'email_verified_at',
            ]),
            'Data profile berhasil diperbarui.'
        );
    }
}
