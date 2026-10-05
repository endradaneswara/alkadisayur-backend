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
        $dashboard = $this->dashboardService->index();

        return $this->success(
            new DashboardResource($dashboard),
            'Data dashboard berhasil diambil.'
        );
    }
}
