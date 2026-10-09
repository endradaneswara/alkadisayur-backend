<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function index(): LengthAwarePaginator
    {
        return User::latest()->paginate(20);
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
