<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesananRequest;
use App\Http\Requests\UpdatePesananRequest;
use App\Http\Resources\PesananResource;
use App\Services\PesananService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PesananService $pesananService
    ) {}

    public function index(Request $request)
    {
        $pesanan = $this->pesananService->index(
            $request->user()->id
        );

        return $this->success(
            PesananResource::collection($pesanan),
            'Daftar pesanan berhasil diambil.'
        );
    }

    public function store(StorePesananRequest $request)
    {
        $pesanan = $this->pesananService->store(
            $request->user()->id,
            $request->validated()
        );

        return $this->success(
            new PesananResource($pesanan),
            'Pesanan berhasil dibuat.',
            201
        );
    }

    public function show(
        Request $request,
        string $id
    ) {
        $pesanan = $this->pesananService->show(
            $request->user()->id,
            $id
        );

        return $this->success(
            new PesananResource($pesanan),
            'Detail pesanan berhasil diambil.'
        );
    }

    public function update(
        UpdatePesananRequest $request,
        string $id
    ) {
        $pesanan = $this->pesananService->update(
            $id,
            $request->validated()
        );

        return $this->success(
            new PesananResource($pesanan),
            'Status pesanan berhasil diperbarui.'
        );
    }

    public function destroy(
        Request $request,
        string $id
    ) {
        $pesanan = $this->pesananService->cancel(
            $request->user()->id,
            $id
        );

        return $this->success(
            new PesananResource($pesanan),
            'Pesanan berhasil dibatalkan.'
        );
    }
}