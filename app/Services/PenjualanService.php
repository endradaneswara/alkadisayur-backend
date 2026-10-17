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
        $lokasiId = ! empty($filters['lokasi_id']) ? (int) $filters['lokasi_id'] : null;
        $query = $this->salesQuery($user, $lokasiId)->with([
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

    public function summary(User $user, array $filters = []): array
    {
        $lokasiId = ! empty($filters['lokasi_id']) ? (int) $filters['lokasi_id'] : null;
        $query = $this->salesQuery($user, $lokasiId);

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

        $totals = $query
            ->selectRaw(
                'COUNT(*) AS total_transaksi, COALESCE(SUM(total_harga), 0) AS total_penjualan, COALESCE(SUM(biaya_pengiriman), 0) AS total_pengiriman'
            )
            ->first();

        return [
            'total_transaksi' => (int) ($totals->total_transaksi ?? 0),
            'total_penjualan' => $totals->total_penjualan ?? 0,
            'total_pengiriman' => $totals->total_pengiriman ?? 0,
            'total_pendapatan' => (float) ($totals->total_penjualan ?? 0)
                + (float) ($totals->total_pengiriman ?? 0),
        ];
    }

    private function salesQuery(User $user, ?int $lokasiId = null): Builder
    {
        $query = Pesanan::query()->where('status', 'selesai');

        if ($user->role === 'admin') {
            if ($user->lokasi_id === null) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('lokasi_toko_id', $user->lokasi_id);
        } elseif ($user->role === 'superAdmin' && $lokasiId !== null) {
            $query->where('lokasi_toko_id', $lokasiId);
        }

        return $query;
    }
}
