<?php

namespace App\Http\Controllers;

use App\Models\UserManagement\User;
use App\Services\CensusDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CensusDashboardController extends Controller
{
    public function __construct(
        protected CensusDashboardService $censusDashboardService,
    ) {}

    /**
     * GET /api/v1/dashboard/tiles
     */
    public function tiles(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json($this->censusDashboardService->tiles($user));
    }

    /**
     * GET /api/v1/dashboard/stats/{statistic}
     */
    public function show(Request $request, string $statistic): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json($this->censusDashboardService->show($user, $statistic, $request->query()));
    }

    /**
     * GET /api/v1/dashboard/stats/{statistic}/export
     */
    public function export(Request $request, string $statistic): Response
    {
        /** @var User $user */
        $user = $request->user();

        return $this->censusDashboardService->export($user, $statistic, $request->query());
    }
}
