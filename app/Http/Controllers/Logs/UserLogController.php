<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Models\Logs\UserLog;
use App\Services\Logs\UserLogService;
use Illuminate\Http\JsonResponse;

class UserLogController extends Controller
{
    public function __construct(
        protected UserLogService $userLogService,
    ) {}

    /**
     * GET /api/v1/user-logs
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', UserLog::class);

        return response()->json([
            'logs' => $this->userLogService->listLoginHistory(),
        ]);
    }
}
