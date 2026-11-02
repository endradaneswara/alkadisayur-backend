<?php

namespace App\Services;

use App\Models\Ulasan;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class UlasanService
{
    public function index(
        ?int $perPage = 20,
        ?int $rating = null,
        ?string $search = null
    ): LengthAwarePaginator {
        return Ulasan::with('user')
            ->when($rating, fn ($query, $r) => $query->where('rating', $r))
            ->when($search, function ($query, $s) {
                $query->where(function ($q) use ($s) {
                    $q->where('komentar', 'like', "%{$s}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
                });
            })
            ->latest()
            ->paginate($perPage ?? 20);
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
        string|int $id,
        ?string $userRole = null
    ): void {
        if (in_array($userRole, ['superAdmin', 'admin'])) {
            $ulasan = Ulasan::findOrFail($id);
        } else {
            $ulasan = Ulasan::where('user_id', $userId)
                ->findOrFail($id);
        }

        $ulasan->delete();
    }
}
