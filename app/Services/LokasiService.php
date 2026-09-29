<?php

namespace App\Services;

use App\Models\Lokasi;
use Illuminate\Database\Eloquent\Collection;

class LokasiService
{
    public function index(): Collection
    {
        return Lokasi::all();
    }

    public function show(string|int $id): Lokasi
    {
        return Lokasi::findOrFail($id);
    }

    public function store(array $data): Lokasi
    {
        return Lokasi::create($data);
    }

    public function update(string|int $id, array $data): Lokasi
    {
        $lokasi = Lokasi::findOrFail($id);

        $lokasi->update($data);

        return $lokasi->fresh();
    }

    public function destroy(string|int $id): void
    {
        Lokasi::findOrFail($id)->delete();
    }
}