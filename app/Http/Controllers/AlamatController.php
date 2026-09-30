<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlamatRequest;
use App\Http\Requests\UpdateAlamatRequest;
use App\Http\Resources\AlamatResource;
use App\Services\AlamatService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AlamatController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AlamatService $alamatService
    ) {}

    public function index(Request $request)
    {
        $alamat = $this->alamatService->index(
            $request->user()->id
        );

        return $this->success(
            AlamatResource::collection($alamat),
            'Daftar alamat berhasil diambil.'
        );
    }

    public function store(StoreAlamatRequest $request)
    {
        $alamat = $this->alamatService->store(
            $request->user()->id,
            $request->validated()
        );

        return $this->success(
            new AlamatResource($alamat),
            'Alamat berhasil ditambahkan.',
            201
        );
    }

    public function show(
        Request $request,
        string $id
    ) {
        $alamat = $this->alamatService->show(
            $request->user()->id,
            $id
        );

        return $this->success(
            new AlamatResource($alamat),
            'Detail alamat berhasil diambil.'
        );
    }

    public function update(
        UpdateAlamatRequest $request,
        string $id
    ) {
        $alamat = $this->alamatService->update(
            $request->user()->id,
            $id,
            $request->validated()
        );

        return $this->success(
            new AlamatResource($alamat),
            'Alamat berhasil diperbarui.'
        );
    }

    public function destroy(
        Request $request,
        string $id
    ) {
        $this->alamatService->destroy(
            $request->user()->id,
            $id
        );

        return $this->success(
            null,
            'Alamat berhasil dihapus.'
        );
    }
}