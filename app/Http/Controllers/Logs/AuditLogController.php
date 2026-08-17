<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logs\IndexAuditLogRequest;
use App\Models\Logs\AuditLog;
use App\Services\Logs\AuditLogService;
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

        return response()->json([
            'logs' => $this->auditLogService->listAuditLogs($request->filters()),
        ]);
    }
}
