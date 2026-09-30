<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUlasanRequest;
use App\Http\Requests\UpdateUlasanRequest;
use App\Http\Resources\UlasanResource;
use App\Services\UlasanService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class UlasanController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected UlasanService $ulasanService
    ) {}

    public function index()
    {
        $ulasan = $this->ulasanService->index();

        return $this->success(
            UlasanResource::collection($ulasan),
            'Daftar ulasan berhasil diambil.'
        );
    }

    public function store(StoreUlasanRequest $request)
    {
        $ulasan = $this->ulasanService->store(
            $request->user()->id,
            $request->validated()
        );

        return $this->success(
            new UlasanResource($ulasan),
            'Ulasan berhasil ditambahkan.',
            201
        );
    }

    public function show(string $id)
    {
        $ulasan = $this->ulasanService->show($id);

        return $this->success(
            new UlasanResource($ulasan),
            'Detail ulasan berhasil diambil.'
        );
    }

    public function update(
        UpdateUlasanRequest $request,
        string $id
    ) {
        $ulasan = $this->ulasanService->update(
            $request->user()->id,
            $id,
            $request->validated()
        );

        return $this->success(
            new UlasanResource($ulasan),
            'Ulasan berhasil diperbarui.'
        );
    }

    public function destroy(Request $request, string $id)
    {
        $this->ulasanService->destroy(
            $request->user()->id,
            $id
        );

        return $this->success(
            null,
            'Ulasan berhasil dihapus.'
        );
    }
}