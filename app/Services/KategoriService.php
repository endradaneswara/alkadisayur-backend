<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Pagination\LengthAwarePaginator;

class KategoriService
{
    /** @return LengthAwarePaginator<int, Kategori> */
    public function index(): LengthAwarePaginator
    {
        return Kategori::query()
            ->orderBy('id')
            ->paginate(20);
    }

    public function show(string|int $id): Kategori
    {
        return Kategori::findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function store(array $data): Kategori
    {
        return Kategori::create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(string|int $id, array $data): Kategori
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->update($data);

        return $kategori->fresh();
    }

    public function destroy(string|int $id): void
    {
        Kategori::findOrFail($id)->delete();
    }

    /** @return LengthAwarePaginator<int, Barang> */
    public function barang(string|int $id): LengthAwarePaginator
    {
        $kategori = Kategori::findOrFail($id);

        return $kategori->barang()
            ->with('kategori')
            ->orderBy('id')
            ->paginate(20);
    }
}
