<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    /**
     * GET /api/v1/dashboard/statistics
     */
    public function statistics(): JsonResponse
    {
        return response()->json($this->dashboardService->statistics());
    }
}
