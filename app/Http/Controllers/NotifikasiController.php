<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotifikasiResource;
use App\Services\NotifikasiService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected NotifikasiService $notifikasiService
    ) {}

    public function index(Request $request)
    {
        $notifikasi = $this->notifikasiService->index(
            $request->user()->id
        );

        return $this->success(
            NotifikasiResource::collection($notifikasi),
            'Daftar notifikasi berhasil diambil.'
        );
    }

    public function show(Request $request, string $id)
    {
        $notifikasi = $this->notifikasiService->show(
            $request->user()->id,
            $id
        );

        return $this->success(
            new NotifikasiResource($notifikasi),
            'Detail notifikasi berhasil diambil.'
        );
    }

    public function read(Request $request, string $id)
    {
        $notifikasi = $this->notifikasiService->read(
            $request->user()->id,
            $id
        );

        return $this->success(
            new NotifikasiResource($notifikasi),
            'Notifikasi berhasil ditandai sudah dibaca.'
        );
    }

    public function readAll(Request $request)
    {
        $this->notifikasiService->readAll(
            $request->user()->id
        );

        return $this->success(
            null,
            'Semua notifikasi berhasil ditandai sudah dibaca.'
        );
    }

    public function destroy(Request $request, string $id)
    {
        $this->notifikasiService->destroy(
            $request->user()->id,
            $id
        );

        return $this->success(
            null,
            'Notifikasi berhasil dihapus.'
        );
    }
}
