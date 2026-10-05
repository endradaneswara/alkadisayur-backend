<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PembayaranService
{
    public function index(string|int $userId): Collection
    {
        return Pembayaran::with('pesanan')
            ->whereHas('pesanan', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->latest()
            ->get();
    }

    public function show(
        string|int $userId,
        string|int $id
    ): Pembayaran {
        return Pembayaran::with('pesanan')
            ->whereHas('pesanan', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->findOrFail($id);
    }

    public function store(
        string|int $userId,
        array $data
    ): Pembayaran {
        $pesanan = Pesanan::where('user_id', $userId)
            ->findOrFail($data['pesanan_id']);

        if ($pesanan->status === 'dibatalkan') {
            throw ValidationException::withMessages([
                'pesanan_id' => [
                    'Pesanan yang sudah dibatalkan tidak dapat dibayar.'
                ],
            ]);
        }

        if ($pesanan->pembayaran) {
            throw ValidationException::withMessages([
                'pesanan_id' => [
                    'Pesanan ini sudah memiliki pembayaran.'
                ],
            ]);
        }

        $pembayaran = Pembayaran::create([
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => $data['metode_pembayaran'],
            'status' => 'pending',
            'jumlah' => $pesanan->total_harga
                + $pesanan->biaya_pengiriman,
            'transaksi_id' => 'TRX-' . strtoupper(
                Str::random(10)
            ),
            'tanggal_pembayaran' => now(),
        ]);

        return $pembayaran->load('pesanan');
    }

    public function update(
        User $user,
        string|int $id,
        array $data
    ): Pembayaran {
        $query = Pembayaran::with('pesanan');

        if ($user->role === 'admin') {
            $query->whereHas('pesanan', function ($query) use ($user) {
                $query->where('lokasi_toko_id', $user->lokasi_id);
            });
        }

        $pembayaran = $query->findOrFail($id);

        $pembayaran->update([
            'status' => $data['status'],
            'tanggal_pembayaran' => $data['status'] === 'berhasil'
                ? now()
                : $pembayaran->tanggal_pembayaran,
        ]);

        return $pembayaran->fresh('pesanan');
    }
}
