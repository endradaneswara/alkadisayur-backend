<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKeranjangItemRequest;
use App\Http\Requests\UpdateKeranjangItemRequest;
use App\Http\Resources\KeranjangResource;
use App\Services\KeranjangService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class KeranjangController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected KeranjangService $keranjangService
    ) {}


    public function index(Request $request)
    {
        $keranjang = $this->keranjangService->index(
            $request->user()->id
        );

        return $this->success(
            new KeranjangResource($keranjang),
            'Keranjang berhasil diambil.'
        );
    }

    public function store(
        StoreKeranjangItemRequest $request
    ) {
        $keranjang = $this->keranjangService->store(
            $request->user()->id,
            $request->validated()
        );

        return $this->success(
            new KeranjangResource($keranjang),
            'Barang berhasil ditambahkan ke keranjang.',
            201
        );
    }

    public function update(
        UpdateKeranjangItemRequest $request,
        string $id
    ) {
        $keranjang = $this->keranjangService->update(
            $request->user()->id,
            $id,
            $request->validated()
        );

        return $this->success(
            new KeranjangResource($keranjang),
            'Jumlah barang berhasil diperbarui.'
        );
    }

    public function destroy(
        Request $request,
        string $id
    ) {
        $this->keranjangService->destroy(
            $request->user()->id,
            $id
        );

        return $this->success(
            null,
            'Barang berhasil dihapus dari keranjang.'
        );
    }

    public function clear(Request $request)
    {
        $this->keranjangService->clear(
            $request->user()->id
        );

        return $this->success(
            null,
            'Keranjang berhasil dikosongkan.'
        );
    }
}