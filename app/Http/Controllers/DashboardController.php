<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    /**
     * GET /api/v1/dashboard/statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        return response()->json($this->dashboardService->statistics($request->query()));
    }

    /**
     * GET /api/v1/dashboard/statistics/overview
     */
    public function overview(Request $request): JsonResponse
    {
        try {
            return response()->json($this->dashboardService->overview($request->query()));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
