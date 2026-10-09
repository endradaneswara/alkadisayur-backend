<?php

namespace App\Services;

use App\Models\Barang;
use Illuminate\Pagination\LengthAwarePaginator;

class BarangService
{
    public function index(): LengthAwarePaginator
    {
        return Barang::with('kategori')
            ->orderBy('id')
            ->paginate(20);
    }

    public function show(string|int $id): Barang
    {
        return Barang::with('kategori')->findOrFail($id);
    }

    public function store(array $data): Barang
    {
        $barang = Barang::create($data);

        return $barang->load('kategori');
    }

    public function update(string|int $id, array $data): Barang
    {
        $barang = Barang::findOrFail($id);
        $barang->update($data);

        return $barang->fresh()->load('kategori');
    }

    public function destroy(string|int $id): void
    {
        Barang::findOrFail($id)->delete();
    }
}
