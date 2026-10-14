<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logs\IndexUserLogRequest;
use App\Models\Logs\UserLog;
use App\Services\Logs\UserLogService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class UserLogController extends Controller
{
    public function __construct(
        protected UserLogService $userLogService,
    ) {}

    /**
     * GET /api/v1/user-logs
     */
    public function index(IndexUserLogRequest $request): JsonResponse
    {
        $this->authorize('viewAny', UserLog::class);

        try {
            $logs = $this->userLogService->listLoginHistory($request->source());
        } catch (QueryException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to load user logs.',
            ], 500);
        }

        return response()->json([
            'logs' => $logs,
        ]);
    }
}
