<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Keranjang;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PesananService
{
    public function index(string|int $userId): Collection
    {
        return Pesanan::with([
            'alamat',
            'lokasiToko',
            'items.barang',
            'pembayaran',
        ])
            ->where('user_id', $userId)
            ->latest('tanggal_pemesanan')
            ->get();
    }

    public function show(
        string|int $userId,
        string|int $id
    ): Pesanan {
        return Pesanan::with([
            'alamat',
            'lokasiToko',
            'items.barang',
            'pembayaran',
        ])
            ->where('user_id', $userId)
            ->findOrFail($id);
    }

    public function store(
        string|int $userId,
        array $data
    ): Pesanan {
        return DB::transaction(function () use ($userId, $data) {

            $keranjang = Keranjang::with('items.barang')
                ->where('user_id', $userId)
                ->firstOrFail();

            if ($keranjang->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'keranjang' => ['Keranjang masih kosong.'],
                ]);
            }

            if ($data['metode'] === 'pesan antar' && empty($data['alamat_id'])) {
                throw ValidationException::withMessages([
                    'alamat_id' => [
                        'Alamat wajib dipilih untuk metode pesan antar.'
                    ],
                ]);
            }

            if ($data['metode'] === 'ambil di toko' && empty($data['lokasi_toko_id'])) {
                throw ValidationException::withMessages([
                    'lokasi_toko_id' => [
                        'Lokasi toko wajib dipilih untuk metode ambil di toko.'
                    ],
                ]);
            }

            foreach ($keranjang->items as $item) {

                if ($item->barang->Stok < $item->jumlah) {
                    throw ValidationException::withMessages([
                        'stok' => [
                            "Stok {$item->barang->NamaItem} tidak mencukupi."
                        ],
                    ]);
                }
            }

            $totalHarga = $keranjang->items->sum(
                fn ($item) => $item->jumlah * $item->barang->HargaJual
            );

            $biayaPengiriman = $data['metode'] === 'pesan antar'
                ? ($data['biaya_pengiriman'] ?? 0)
                : 0;

            $pesanan = Pesanan::create([
                'user_id' => $userId,
                'alamat_id' => $data['metode'] === 'pesan antar'
                    ? $data['alamat_id']
                    : null,

                'lokasi_toko_id' => $data['metode'] === 'ambil di toko'
                    ? $data['lokasi_toko_id']
                    : null,

                'nomor_order' => 'ORD-' . strtoupper(
                    Str::random(10)
                ),

                'total_harga' => $totalHarga,
                'biaya_pengiriman' => $biayaPengiriman,
                'metode' => $data['metode'],
                'status' => 'pending',
                'catatan' => $data['catatan'] ?? null,
                'tanggal_pemesanan' => now(),
            ]);

            foreach ($keranjang->items as $item) {

                $harga = $item->barang->HargaJual;

                $pesanan->items()->create([
                    'barang_id' => $item->barang_id,
                    'jumlah' => $item->jumlah,
                    'harga' => $harga,
                    'subtotal' => $item->jumlah * $harga,
                ]);

                $item->barang->decrement(
                    'Stok',
                    $item->jumlah
                );
            }

            $keranjang->items()->delete();

            return $pesanan->load([
                'alamat',
                'lokasiToko',
                'items.barang',
                'pembayaran',
            ]);
        });
    }

    public function update(
        string|int $id,
        array $data
    ): Pesanan {
        $pesanan = Pesanan::findOrFail($id);

        $pesanan->update($data);

        return $pesanan->fresh([
            'alamat',
            'lokasiToko',
            'items.barang',
            'pembayaran',
        ]);
    }

    public function cancel(
        string|int $userId,
        string|int $id
    ): Pesanan {
        $pesanan = Pesanan::where('user_id', $userId)
            ->findOrFail($id);

        if (
            in_array($pesanan->status, [
                'selesai',
                'dibatalkan',
            ])
        ) {
            throw ValidationException::withMessages([
                'status' => [
                    'Pesanan tidak dapat dibatalkan.'
                ],
            ]);
        }

        $pesanan->update([
            'status' => 'dibatalkan',
        ]);

        foreach ($pesanan->items as $item) {
            $item->barang->increment(
                'Stok',
                $item->jumlah
            );
        }

        return $pesanan->fresh([
            'items.barang',
            'alamat',
            'lokasiToko',
        ]);
    }
}