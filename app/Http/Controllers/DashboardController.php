<?php

namespace App\Http\Controllers;

use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use App\Traits\ApiResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index()
    {
        $validated = request()->validate([
            'rentang' => ['sometimes', 'string', 'in:1h,7h,30h'],
            'lokasi_id' => ['sometimes', 'nullable', 'integer', 'exists:lokasi,id'],
        ]);

        $rentang = $validated['rentang'] ?? '7h';
        $lokasiId = isset($validated['lokasi_id']) ? (int) $validated['lokasi_id'] : null;

        $dashboard = $this->dashboardService->index(
            request()->user(),
            $rentang,
            $lokasiId
        );

        return $this->success(
            new DashboardResource($dashboard),
            'Data dashboard berhasil diambil.'
        );
    }
}
