<?php

namespace App\Services;

use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Collection;

class PenjualanService
{
    public function index(array $filters = []): Collection
    {
        $query = Pesanan::with([
            'user',
            'alamat',
            'lokasiToko',
            'items.barang',
            'pembayaran',
        ])
        ->where('status', 'selesai')
        ->latest('tanggal_pemesanan');

        if (!empty($filters['tanggal_mulai'])) {
            $query->whereDate(
                'tanggal_pemesanan',
                '>=',
                $filters['tanggal_mulai']
            );
        }

        if (!empty($filters['tanggal_selesai'])) {
            $query->whereDate(
                'tanggal_pemesanan',
                '<=',
                $filters['tanggal_selesai']
            );
        }

        return $query->get();
    }

    public function show(string|int $id): Pesanan
    {
        return Pesanan::with([
            'user',
            'alamat',
            'lokasiToko',
            'items.barang',
            'pembayaran',
        ])
        ->where('status', 'selesai')
        ->findOrFail($id);
    }

    public function summary(): array
    {
        $query = Pesanan::where('status', 'selesai');

        return [
            'total_transaksi' => $query->count(),
            'total_penjualan' => $query->sum('total_harga'),
            'total_pengiriman' => $query->sum('biaya_pengiriman'),
            'total_pendapatan' => $query->sum('total_harga')
                + $query->sum('biaya_pengiriman'),
        ];
    }
}
