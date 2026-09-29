<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLokasiRequest;
use App\Http\Requests\UpdateLokasiRequest;
use App\Http\Resources\LokasiResource;
use App\Services\LokasiService;
use App\Traits\ApiResponse;

class LokasiController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected LokasiService $lokasiService
    ) {}

    public function index()
    {
        $lokasi = $this->lokasiService->index();

        return $this->success(
            LokasiResource::collection($lokasi),
            'Daftar lokasi berhasil diambil.'
        );
    }

    public function store(StoreLokasiRequest $request)
    {
        $lokasi = $this->lokasiService->store(
            $request->validated()
        );

        return $this->success(
            new LokasiResource($lokasi),
            'Lokasi berhasil ditambahkan.',
            201
        );
    }

    public function show(string $id)
    {
        $lokasi = $this->lokasiService->show($id);

        return $this->success(
            new LokasiResource($lokasi),
            'Detail lokasi berhasil diambil.'
        );
    }

    public function update(
        UpdateLokasiRequest $request,
        string $id
    ) {
        $lokasi = $this->lokasiService->update(
            $id,
            $request->validated()
        );

        return $this->success(
            new LokasiResource($lokasi),
            'Lokasi berhasil diperbarui.'
        );
    }

    public function destroy(string $id)
    {
        $this->lokasiService->destroy($id);

        return $this->success(
            null,
            'Lokasi berhasil dihapus.'
        );
    }
}