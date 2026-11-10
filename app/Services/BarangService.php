<?php

namespace App\Services;

use App\Models\Barang;
use Illuminate\Pagination\LengthAwarePaginator;

class BarangService
{
    /** @return LengthAwarePaginator<int, Barang> */
    public function index(?string $search = null): LengthAwarePaginator
    {
        $term = $search === null ? '' : trim($search);

        if ($term === '') {
            return Barang::with('kategori')
                ->orderBy('id')
                ->paginate(20);
        }

        $pattern = '%'.$term.'%';
        $prefix = $term.'%';

        return Barang::with('kategori')
            ->where(function ($query) use ($pattern): void {
                $query
                    ->where('NamaItem', 'like', $pattern)
                    ->orWhere('Merek', 'like', $pattern)
                    ->orWhere('Keterangan', 'like', $pattern)
                    ->orWhereHas('kategori', fn ($kategori) => $kategori->where('nama_kategori', 'like', $pattern)
                    );
            })
            ->orderByRaw(
                'CASE
                    WHEN LOWER(NamaItem) = LOWER(?) THEN 0
                    WHEN LOWER(NamaItem) LIKE LOWER(?) THEN 1
                    WHEN LOWER(NamaItem) LIKE LOWER(?) THEN 2
                    WHEN EXISTS (
                        SELECT 1 FROM kategori
                        WHERE kategori.id = barang.kategori_id
                        AND LOWER(kategori.nama_kategori) LIKE LOWER(?)
                    ) THEN 3
                    ELSE 4
                END',
                [$term, $prefix, $pattern, $prefix]
            )
            ->orderBy('NamaItem')
            ->orderBy('id')
            ->paginate(20);
    }

    public function show(string|int $id): Barang
    {
        return Barang::with('kategori')->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function store(array $data): Barang
    {
        if (isset($data['Stok'])) {
            $data['Status'] = (int) $data['Stok'] === 0 ? 'tidak tersedia' : 'tersedia';
        }

        $barang = Barang::create($data);

        return $barang->load('kategori');
    }

    /** @param array<string, mixed> $data */
    public function update(string|int $id, array $data): Barang
    {
        $barang = Barang::findOrFail($id);

        if (isset($data['Stok'])) {
            $data['Status'] = (int) $data['Stok'] === 0 ? 'tidak tersedia' : 'tersedia';
        }

        $barang->update($data);

        return $barang->fresh()->load('kategori');
    }

    public function destroy(string|int $id): void
    {
        Barang::findOrFail($id)->delete();
    }
}
