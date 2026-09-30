<?php

namespace App\Services;

use App\Models\Alamat;
use Illuminate\Database\Eloquent\Collection;

class AlamatService
{
    public function index(string|int $userId): Collection
    {
        return Alamat::where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function show(
        string|int $userId,
        string|int $id
    ): Alamat {
        return Alamat::where('user_id', $userId)
            ->findOrFail($id);
    }

    public function store(
        string|int $userId,
        array $data
    ): Alamat {
        return Alamat::create([
            'user_id' => $userId,
            'nama_alamat' => $data['nama_alamat'],
            'nama_penerima' => $data['nama_penerima'],
            'no_hp' => $data['no_hp'],
            'alamat_lengkap' => $data['alamat_lengkap'],
        ]);
    }

    public function update(
        string|int $userId,
        string|int $id,
        array $data
    ): Alamat {
        $alamat = $this->show($userId, $id);

        $alamat->update($data);

        return $alamat->fresh();
    }

    public function destroy(
        string|int $userId,
        string|int $id
    ): void {
        $alamat = $this->show($userId, $id);

        $alamat->delete();
    }
}