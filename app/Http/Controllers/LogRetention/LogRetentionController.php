<?php

namespace App\Http\Controllers\LogRetention;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogRetention\ArchiveLogRetentionRequest;
use App\Http\Requests\LogRetention\PreviewLogRetentionRequest;
use App\Http\Requests\LogRetention\UpdateLogRetentionRequest;
use App\Models\Logs\AuditLogArchive;
use App\Models\UserManagement\User;
use App\Services\LogRetention\LogRetentionService;
use Illuminate\Http\JsonResponse;

class LogRetentionController extends Controller
{
    public function __construct(
        protected LogRetentionService $logRetentionService,
    ) {}

    /**
     * GET /api/v1/log-retention
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', AuditLogArchive::class);

        return response()->json($this->logRetentionService->summary());
    }

    /**
     * PUT /api/v1/log-retention
     */
    public function update(UpdateLogRetentionRequest $request): JsonResponse
    {
        $this->authorize('update', AuditLogArchive::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        /** @var array{audit_log_retention_days: int, user_log_retention_days: int} $days */
        $days = $request->validated();

        $result = $this->logRetentionService->update($performedBy, $days);

        return response()->json([
            'message' => $result['changed'] === 0
                ? 'Log retention is unchanged.'
                : 'Log retention updated.',
            'audit' => $result['audit'],
            'user' => $result['user'],
        ]);
    }

    /**
     * GET /api/v1/log-retention/preview
     */
    public function preview(PreviewLogRetentionRequest $request): JsonResponse
    {
        $this->authorize('archive', AuditLogArchive::class);

        $log = (string) $request->validated('log');

        return response()->json([
            'log' => $log,
            'count' => $this->logRetentionService->preview($log),
        ]);
    }

    /**
     * POST /api/v1/log-retention/archive
     */
    public function archive(ArchiveLogRetentionRequest $request): JsonResponse
    {
        $this->authorize('archive', AuditLogArchive::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $log = (string) $request->validated('log');
        $archived = $this->logRetentionService->archive($performedBy, $log);
        $label = $log === 'audit' ? 'audit log' : 'user log';

        return response()->json([
            'message' => "Archived {$archived} {$label} records.",
            'log' => $log,
            'archived' => $archived,
        ]);
    }
}
