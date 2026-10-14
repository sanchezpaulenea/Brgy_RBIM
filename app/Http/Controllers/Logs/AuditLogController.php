<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logs\IndexAuditLogRequest;
use App\Models\Logs\AuditLog;
use App\Services\Logs\AuditLogService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    /**
     * GET /api/v1/audit-logs
     */
    public function index(IndexAuditLogRequest $request): JsonResponse
    {
        $this->authorize('viewAny', AuditLog::class);

        try {
            $logs = $this->auditLogService->listAuditLogs($request->filters());
        } catch (QueryException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to load audit logs.',
            ], 500);
        }

        return response()->json([
            'logs' => $logs,
        ]);
    }
}
