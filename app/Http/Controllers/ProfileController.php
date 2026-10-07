<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
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
            ]),
            'Data profile berhasil diambil.'
        );
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();

        $user->update($request->validated());

        return $this->success(
            $user->only([
                'id',
                'name',
                'email',
                'no_hp',
                'avatar',
                'role',
            ]),
            'Data profile berhasil diperbarui.'
        );
    }
}
