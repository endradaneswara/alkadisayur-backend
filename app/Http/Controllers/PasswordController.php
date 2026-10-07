<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordUpdateRequest;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    use ApiResponse;

    public function update(PasswordUpdateRequest $request)
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->success(
            null,
            'Password berhasil diperbarui.'
        );
    }
}
