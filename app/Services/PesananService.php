<?php

namespace App\Services;

use App\Models\Keranjang;
use App\Models\Lokasi;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PesananService
{
    public function index(string|int $userId): LengthAwarePaginator
    {
        return Pesanan::with([
            'alamat',
            'lokasiToko',
            'items.barang',
            'pembayaran',
        ])
            ->where('user_id', $userId)
            ->latest('tanggal_pemesanan')
            ->paginate(20);
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

            $keranjang = Keranjang::where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            $items = $keranjang->items()
                ->with('barang')
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'keranjang' => ['Keranjang masih kosong.'],
                ]);
            }

            if (empty($data['lokasi_toko_id'])) {
                throw ValidationException::withMessages([
                    'lokasi_toko_id' => [
                        'Lokasi toko wajib dipilih.',
                    ],
                ]);
            }

            if (
                $data['metode'] === 'pesan_antar'
                && empty($data['alamat_id'])
            ) {
                throw ValidationException::withMessages([
                    'alamat_id' => [
                        'Alamat wajib dipilih untuk metode pesan antar.',
                    ],
                ]);
            }

            $lokasi = Lokasi::findOrFail(
                $data['lokasi_toko_id']
            );

            foreach ($items as $item) {

                $barang = $item->barang()
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($barang->Status !== 'tersedia') {
                    throw ValidationException::withMessages([
                        'barang' => [
                            "Barang {$barang->NamaItem} sudah tidak tersedia.",
                        ],
                    ]);
                }

                if ($barang->Stok < $item->jumlah) {
                    throw ValidationException::withMessages([
                        'stok' => [
                            "Stok {$barang->NamaItem} tidak mencukupi.",
                        ],
                    ]);
                }

                $item->setRelation('barang', $barang);
            }

            $totalHarga = $items->sum(
                fn ($item) => $item->jumlah * $item->barang->HargaJual
            );

            $biayaPengiriman = $data['metode'] === 'pesan_antar'
                ? $lokasi->biaya_pengiriman
                : 0;

            $pesanan = Pesanan::create([
                'user_id' => $userId,

                'alamat_id' => $data['metode'] === 'pesan_antar'
                    ? $data['alamat_id']
                    : null,

                'lokasi_toko_id' => $data['lokasi_toko_id'],

                'nomor_order' => 'ORD-'.strtoupper(
                    Str::random(10)
                ),

                'total_harga' => $totalHarga,
                'biaya_pengiriman' => $biayaPengiriman,

                'metode' => $data['metode'],
                'status' => 'pending',

                'catatan' => $data['catatan'] ?? null,

                'tanggal_pemesanan' => now(),
            ]);

            foreach ($items as $item) {

                $harga = $item->barang->HargaJual;

                $pesanan->items()->create([
                    'barang_id' => $item->barang_id,
                    'nama_barang' => $item->barang->NamaItem,
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

    public function cancel(
        string|int $userId,
        string|int $id
    ): Pesanan {
        return DB::transaction(function () use ($userId, $id): Pesanan {
            $pesanan = Pesanan::where('user_id', $userId)
                ->lockForUpdate()
                ->findOrFail($id);

            if (! in_array($pesanan->status, ['pending', 'diproses'], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Pesanan tidak dapat dibatalkan.'],
                ]);
            }

            foreach ($pesanan->items as $item) {
                $barang = $item->barang()
                    ->lockForUpdate()
                    ->firstOrFail();

                $barang->increment('Stok', $item->jumlah);
            }

            $pesanan->update(['status' => 'dibatalkan']);

            return $pesanan->fresh([
                'items.barang',
                'alamat',
                'lokasiToko',
                'pembayaran',
            ]);
        });
    }

    public function adminIndex(User $user): LengthAwarePaginator
    {
        $query = Pesanan::with([
            'user',
            'alamat',
            'lokasiToko',
            'items.barang',
            'pembayaran',
        ])
            ->latest('tanggal_pemesanan');

        if ($user->role === 'admin') {
            if ($user->lokasi_id === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('lokasi_toko_id', $user->lokasi_id);
            }
        }

        return $query->paginate(20);
    }

    public function adminShow(
        User $user,
        string|int $id
    ): Pesanan {
        $query = Pesanan::with([
            'user',
            'alamat',
            'lokasiToko',
            'items.barang',
            'pembayaran',
        ]);

        if ($user->role === 'admin') {
            if ($user->lokasi_id === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('lokasi_toko_id', $user->lokasi_id);
            }
        }

        return $query->findOrFail($id);
    }

    public function updateStatus(
        User $user,
        string|int $id,
        array $data
    ): Pesanan {
        return DB::transaction(function () use ($user, $id, $data): Pesanan {
            $query = Pesanan::query();

            if ($user->role === 'admin') {
                if ($user->lokasi_id === null) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->where('lokasi_toko_id', $user->lokasi_id);
                }
            }

            $pesanan = $query
                ->lockForUpdate()
                ->findOrFail($id);
            $statusBaru = $data['status'];
            $statusBerikutnya = [
                'pending' => ['diproses', 'dibatalkan'],
                'diproses' => ['dikirim', 'dibatalkan'],
                'dikirim' => ['selesai'],
            ];

            if (! in_array($statusBaru, $statusBerikutnya[$pesanan->status] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => [
                        in_array($pesanan->status, ['selesai', 'dibatalkan'], true)
                            ? 'Status pesanan sudah final dan tidak dapat diubah.'
                            : 'Perubahan status pesanan tidak valid.',
                    ],
                ]);
            }

            if ($statusBaru === 'dibatalkan') {
                foreach ($pesanan->items as $item) {
                    $barang = $item->barang()
                        ->lockForUpdate()
                        ->firstOrFail();

                    $barang->increment('Stok', $item->jumlah);
                }
            }

            $pesanan->update(['status' => $statusBaru]);

            return $pesanan->fresh([
                'user',
                'alamat',
                'lokasiToko',
                'items.barang',
                'pembayaran',
            ]);
        });
    }
}
