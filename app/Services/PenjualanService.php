<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PenjualanService
{
    public function index(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $this->salesQuery($user)->with([
            'user',
            'alamat',
            'lokasiToko',
            'items.barang',
            'pembayaran',
        ])
            ->latest('tanggal_pemesanan');

        if (! empty($filters['tanggal_mulai'])) {
            $query->whereDate(
                'tanggal_pemesanan',
                '>=',
                $filters['tanggal_mulai']
            );
        }

        if (! empty($filters['tanggal_selesai'])) {
            $query->whereDate(
                'tanggal_pemesanan',
                '<=',
                $filters['tanggal_selesai']
            );
        }

        return $query->paginate(20);
    }

    public function show(User $user, string|int $id): Pesanan
    {
        return $this->salesQuery($user)->with([
            'user',
            'alamat',
            'lokasiToko',
            'items.barang',
            'pembayaran',
        ])
            ->findOrFail($id);
    }

    public function summary(User $user): array
    {
        $totals = $this->salesQuery($user)
            ->selectRaw(
                'COUNT(*) AS total_transaksi, COALESCE(SUM(total_harga), 0) AS total_penjualan, COALESCE(SUM(biaya_pengiriman), 0) AS total_pengiriman'
            )
            ->first();

        return [
            'total_transaksi' => (int) $totals->total_transaksi,
            'total_penjualan' => $totals->total_penjualan,
            'total_pengiriman' => $totals->total_pengiriman,
            'total_pendapatan' => (float) $totals->total_penjualan
                + (float) $totals->total_pengiriman,
        ];
    }

    private function salesQuery(User $user): Builder
    {
        $query = Pesanan::query()->where('status', 'selesai');

        if ($user->role === 'admin') {
            if ($user->lokasi_id === null) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('lokasi_toko_id', $user->lokasi_id);
        }

        return $query;
    }
}
