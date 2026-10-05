<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function index(): Collection
    {
        return User::latest()->get();
    }

    public function show(string|int $id): User
    {
        return User::findOrFail($id);
    }

    public function updateRole(string|int $id, string $role): User
    {
        $user = User::findOrFail($id);

        $user->update([
            'role' => $role,
        ]);

        return $user->fresh();
    }
}
