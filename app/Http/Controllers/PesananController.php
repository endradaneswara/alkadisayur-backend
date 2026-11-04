<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesananRequest;
use App\Http\Requests\UpdatePesananRequest;
use App\Http\Resources\PesananResource;
use App\Services\PesananService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PesananService $pesananService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $pesanan = $this->pesananService->index(
            $request->user()->id
        );

        return $this->success(
            PesananResource::collection($pesanan),
            'Daftar pesanan berhasil diambil.'
        );
    }

    public function store(StorePesananRequest $request): JsonResponse
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

    public function show(Request $request, string $id): JsonResponse
    {
        $pesanan = $this->pesananService->show(
            $request->user()->id,
            $id
        );

        return $this->success(
            new PesananResource($pesanan),
            'Detail pesanan berhasil diambil.'
        );
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $pesanan = $this->pesananService->cancel(
            $request->user()->id,
            $id
        );

        return $this->success(
            new PesananResource($pesanan),
            'Pesanan berhasil dibatalkan.'
        );
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $user = $request->user();

        $filters = $request->validate([
            'status' => ['sometimes', 'nullable', 'string', 'in:pending,diproses,dikirim,selesai,dibatalkan'],
            'lokasi_id' => ['sometimes', 'nullable', 'integer', 'exists:lokasi,id'],
            'search' => ['sometimes', 'nullable', 'string'],
        ]);

        $pesanan = $this->pesananService->adminIndex(
            $user,
            $filters
        );

        return $this->success(
            PesananResource::collection($pesanan),
            'Daftar pesanan masuk berhasil diambil.'
        );
    }

    public function adminShow(Request $request, string $id): JsonResponse
    {
        $pesanan = $this->pesananService->adminShow(
            $request->user(),
            $id
        );

        return $this->success(
            new PesananResource($pesanan),
            'Detail pesanan berhasil diambil.'
        );
    }

    public function updateStatus(
        UpdatePesananRequest $request,
        string $id
    ): JsonResponse {
        $pesanan = $this->pesananService->updateStatus(
            $request->user(),
            $id,
            $request->validated()
        );

        return $this->success(
            new PesananResource($pesanan),
            'Status pesanan berhasil diperbarui.'
        );
    }
}
