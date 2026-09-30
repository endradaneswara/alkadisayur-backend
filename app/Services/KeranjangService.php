<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Keranjang;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KeranjangService
{
    public function index(string|int $userId): Keranjang
    {
        return Keranjang::with([
            'items.barang.kategori',
        ])
            ->where('user_id', $userId)
            ->firstOrCreate([
                'user_id' => $userId,
            ]);
    }

    public function store(string|int $userId, array $data): Keranjang
    {
        return DB::transaction(function () use ($userId, $data) {

            $barang = Barang::findOrFail($data['barang_id']);

            if ($barang->status !== 'tersedia') {
                throw ValidationException::withMessages([
                    'barang_id' => ['Barang sedang tidak tersedia.'],
                ]);
            }

            if ($barang->Stok < $data['jumlah']) {
                throw ValidationException::withMessages([
                    'jumlah' => ['Jumlah barang melebihi stok yang tersedia.'],
                ]);
            }

            $keranjang = Keranjang::firstOrCreate([
                'user_id' => $userId,
            ]);

            $item = $keranjang->items()
                ->where('barang_id', $barang->id)
                ->first();

            if ($item) {
                $jumlahBaru = $item->jumlah + $data['jumlah'];

                if ($jumlahBaru > $barang->Stok) {
                    throw ValidationException::withMessages([
                        'jumlah' => ['Jumlah barang melebihi stok yang tersedia.'],
                    ]);
                }

                $item->update([
                    'jumlah' => $jumlahBaru,
                    'harga' => $barang->HargaJual,
                ]);
            } else {
                $keranjang->items()->create([
                    'barang_id' => $barang->id,
                    'jumlah' => $data['jumlah'],
                    'harga' => $barang->HargaJual,
                ]);
            }

            return $keranjang->load([
                'items.barang.kategori',
            ]);
        });
    }

    public function update(
        string|int $userId,
        string|int $itemId,
        array $data
    ): Keranjang {
        $keranjang = Keranjang::where('user_id', $userId)
            ->firstOrFail();

        $item = $keranjang->items()
            ->with('barang')
            ->findOrFail($itemId);

        if ($data['jumlah'] > $item->barang->Stok) {
            throw ValidationException::withMessages([
                'jumlah' => ['Jumlah barang melebihi stok yang tersedia.'],
            ]);
        }

        $item->update([
            'jumlah' => $data['jumlah'],
            'harga' => $item->barang->HargaJual,
        ]);

        return $keranjang->load([
            'items.barang.kategori',
        ]);
    }

    public function destroy(
        string|int $userId,
        string|int $itemId
    ): void {
        $keranjang = Keranjang::where('user_id', $userId)
            ->firstOrFail();

        $keranjang->items()
            ->findOrFail($itemId)
            ->delete();
    }

    public function clear(string|int $userId): void
    {
        $keranjang = Keranjang::where('user_id', $userId)
            ->firstOrFail();

        $keranjang->items()->delete();
    }
}