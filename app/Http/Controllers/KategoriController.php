<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Http\Resources\KategoriResource;
use App\Http\Resources\BarangResource;
use App\Services\KategoriService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Collection;

class KategoriController extends Controller
{
    use ApiResponse;

    public function __construct(protected KategoriService $kategoriService) {}

    public function index()
    {
        $kategori = $this->kategoriService->index();

        return $this->success(
            KategoriResource::collection($kategori),
            'Daftar kategori berhasil diambil.'
        );
    }

    public function store(StoreKategoriRequest $request)
    {
        $kategori = $this->kategoriService->store($request->validated());

        return $this->success(
            new KategoriResource($kategori),
            'Kategori berhasil ditambahkan.',
            201
        );
    }

    public function show(string $id)
    {
        $kategori = $this->kategoriService->show($id);

        return $this->success(
            new KategoriResource($kategori),
            'Detail kategori berhasil diambil.'
        );
    }

    public function update(UpdateKategoriRequest $request, string $id)
    {
        $kategori = $this->kategoriService->update($id, $request->validated());

        return $this->success(
            new KategoriResource($kategori),
            'Kategori berhasil diperbarui.'
        );
    }

    public function destroy(string $id)
    {
        $this->kategoriService->destroy($id);

        return $this->success(null, 'Kategori berhasil dihapus.');
    }

    public function barang(string $id)
    {
    $barang = $this->kategoriService->barang($id);

    return $this->success(
        BarangResource::collection($barang),
        'Daftar barang berdasarkan kategori berhasil diambil.'
    );
    }
}