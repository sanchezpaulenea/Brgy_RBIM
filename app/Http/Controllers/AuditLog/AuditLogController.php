<?php

namespace App\Http\Controllers\AuditLog;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditLog\IndexAuditLogRequest;
use App\Models\AuditLog\AuditLog;
use App\Services\AuditLog\AuditLogService;
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
