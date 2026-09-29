<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Http\Resources\BarangResource;
use App\Services\BarangService;
use App\Traits\ApiResponse;

class BarangController extends Controller
{
    use ApiResponse;

    public function __construct(protected BarangService $barangService) {}

    public function index()
    {
        $barang = $this->barangService->index();

        return $this->success(
            BarangResource::collection($barang),
            'Daftar barang berhasil diambil.'
        );
    }

    public function store(StoreBarangRequest $request)
    {
        $barang = $this->barangService->store($request->validated());

        return $this->success(
            new BarangResource($barang),
            'Barang berhasil ditambahkan.',
            201
        );
    }

    public function show(string $id)
    {
        $barang = $this->barangService->show($id);

        return $this->success(
            new BarangResource($barang),
            'Detail barang berhasil diambil.'
        );
    }

    public function update(UpdateBarangRequest $request, string $id)
    {
        $barang = $this->barangService->update(
            $id,
            $request->validated()
        );

        return $this->success(
            new BarangResource($barang),
            'Barang berhasil diperbarui.'
        );
    }

    public function destroy(string $id)
    {
        $this->barangService->destroy($id);

        return $this->success(
            null,
            'Barang berhasil dihapus.'
        );
    }
}