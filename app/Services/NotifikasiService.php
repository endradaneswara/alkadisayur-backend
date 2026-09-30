<?php

namespace App\Services;

use App\Models\Notifikasi;
use Illuminate\Database\Eloquent\Collection;

class NotifikasiService
{
    public function index(string|int $userId): Collection
    {
        return Notifikasi::with('pesanan')
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function show(
        string|int $userId,
        string|int $id
    ): Notifikasi {
        return Notifikasi::with('pesanan')
            ->where('user_id', $userId)
            ->findOrFail($id);
    }

    public function read(
        string|int $userId,
        string|int $id
    ): Notifikasi {
        $notifikasi = $this->show($userId, $id);

        $notifikasi->update([
            'dibaca_pada' => now(),
        ]);

        return $notifikasi->fresh('pesanan');
    }

    public function readAll(string|int $userId): void
    {
        Notifikasi::where('user_id', $userId)
            ->whereNull('dibaca_pada')
            ->update([
                'dibaca_pada' => now(),
            ]);
    }

    public function destroy(
        string|int $userId,
        string|int $id
    ): void {
        $notifikasi = $this->show($userId, $id);

        $notifikasi->delete();
    }
}
