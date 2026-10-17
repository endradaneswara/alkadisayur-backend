<?php

namespace App\Http\Controllers;

use App\Http\Resources\PenjualanResource;
use App\Services\PenjualanService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PenjualanController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PenjualanService $penjualanService
    ) {}

    public function index(Request $request)
    {
        $endDateRules = ['sometimes', 'nullable', 'date'];

        if ($request->filled('tanggal_mulai')) {
            $endDateRules[] = 'after_or_equal:tanggal_mulai';
        }

        $filters = $request->validate([
            'tanggal_mulai' => ['sometimes', 'nullable', 'date'],
            'tanggal_selesai' => $endDateRules,
            'lokasi_id' => ['sometimes', 'nullable', 'integer', 'exists:lokasi,id'],
        ]);

        $penjualan = $this->penjualanService->index(
            $request->user(),
            $filters
        );

        return $this->success(
            PenjualanResource::collection($penjualan),
            'Daftar penjualan berhasil diambil.'
        );
    }

    public function show(Request $request, string $id)
    {
        $penjualan = $this->penjualanService->show(
            $request->user(),
            $id
        );

        return $this->success(
            new PenjualanResource($penjualan),
            'Detail penjualan berhasil diambil.'
        );
    }

    public function summary(Request $request)
    {
        $endDateRules = ['sometimes', 'nullable', 'date'];

        if ($request->filled('tanggal_mulai')) {
            $endDateRules[] = 'after_or_equal:tanggal_mulai';
        }

        $filters = $request->validate([
            'tanggal_mulai' => ['sometimes', 'nullable', 'date'],
            'tanggal_selesai' => $endDateRules,
            'lokasi_id' => ['sometimes', 'nullable', 'integer', 'exists:lokasi,id'],
        ]);

        return $this->success(
            $this->penjualanService->summary($request->user(), $filters),
            'Ringkasan penjualan berhasil diambil.'
        );
    }
}
