<?php

namespace App\Services;

use App\Models\Kategori;
use Illuminate\Pagination\LengthAwarePaginator;

class KategoriService
{
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

    public function store(array $data): Kategori
    {
        return Kategori::create($data);
    }

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

    public function barang(string|int $id): LengthAwarePaginator
    {
        $kategori = Kategori::findOrFail($id);

        return $kategori->barang()
            ->with('kategori')
            ->orderBy('id')
            ->paginate(20);
    }
}
