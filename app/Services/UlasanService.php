<?php

namespace App\Services;

use App\Models\Ulasan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class UlasanService
{
    public function index(): Collection
    {
        return Ulasan::with('user')
            ->latest()
            ->get();
    }

    public function show(string|int $id): Ulasan
    {
        return Ulasan::with('user')
            ->findOrFail($id);
    }

    public function store(string|int $userId, array $data): Ulasan
    {
        $sudahAda = Ulasan::where('user_id', $userId)->exists();

        if ($sudahAda) {
            throw ValidationException::withMessages([
                'ulasan' => ['Anda sudah memberikan ulasan.'],
            ]);
        }

        return Ulasan::create([
            'user_id' => $userId,
            'rating' => $data['rating'],
            'komentar' => $data['komentar'] ?? null,
        ])->load('user');
    }

    public function update(
        string|int $userId,
        string|int $id,
        array $data
    ): Ulasan {
        $ulasan = Ulasan::where('user_id', $userId)
            ->findOrFail($id);

        $ulasan->update($data);

        return $ulasan->fresh('user');
    }

    public function destroy(
        string|int $userId,
        string|int $id
    ): void {
        $ulasan = Ulasan::where('user_id', $userId)
            ->findOrFail($id);

        $ulasan->delete();
    }
}