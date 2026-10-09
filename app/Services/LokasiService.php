<?php

namespace App\Services;

use App\Models\Lokasi;
use Illuminate\Pagination\LengthAwarePaginator;

class LokasiService
{
    public function index(): LengthAwarePaginator
    {
        return Lokasi::query()
            ->orderBy('id')
            ->paginate(20);
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
