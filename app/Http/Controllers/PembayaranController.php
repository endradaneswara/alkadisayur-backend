<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePembayaranRequest;
use App\Http\Requests\UpdatePembayaranRequest;
use App\Http\Resources\PembayaranResource;
use App\Services\PembayaranService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PembayaranService $pembayaranService
    ) {}

    public function index(Request $request)
    {
        $pembayaran = $this->pembayaranService->index(
            $request->user()->id
        );

        return $this->success(
            PembayaranResource::collection($pembayaran),
            'Daftar pembayaran berhasil diambil.'
        );
    }

    public function store(StorePembayaranRequest $request)
    {
        $pembayaran = $this->pembayaranService->store(
            $request->user()->id,
            $request->validated()
        );

        return $this->success(
            new PembayaranResource($pembayaran),
            'Pembayaran berhasil dibuat.',
            201
        );
    }

    public function show(
        Request $request,
        string $id
    ) {
        $pembayaran = $this->pembayaranService->show(
            $request->user()->id,
            $id
        );

        return $this->success(
            new PembayaranResource($pembayaran),
            'Detail pembayaran berhasil diambil.'
        );
    }

    public function update(
        UpdatePembayaranRequest $request,
        string $id
    ) {
        $pembayaran = $this->pembayaranService->update(
            $id,
            $request->validated()
        );

        return $this->success(
            new PembayaranResource($pembayaran),
            'Status pembayaran berhasil diperbarui.'
        );
    }
}