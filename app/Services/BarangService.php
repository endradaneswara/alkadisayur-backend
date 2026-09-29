<?php

namespace App\Services;

use App\Models\Barang;
use Illuminate\Database\Eloquent\Collection;

class BarangService
{
    public function index(): Collection
    {
        return Barang::with('kategori')->get();
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