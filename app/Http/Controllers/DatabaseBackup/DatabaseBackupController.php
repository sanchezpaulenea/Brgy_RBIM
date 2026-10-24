<?php

namespace App\Http\Controllers\DatabaseBackup;

use App\Http\Controllers\Controller;
use App\Http\Requests\DatabaseBackup\ExportDatabaseBackupRequest;
use App\Http\Requests\DatabaseBackup\RestoreDatabaseBackupRequest;
use App\Models\DatabaseBackup;
use App\Models\UserManagement\User;
use App\Services\DatabaseBackup\DatabaseBackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class DatabaseBackupController extends Controller
{
    public function __construct(
        protected DatabaseBackupService $databaseBackupService,
    ) {}

    /**
     * POST /api/v1/database-backups/export
     */
    public function export(ExportDatabaseBackupRequest $request): Response
    {
        $this->authorize('export', DatabaseBackup::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $file = $this->databaseBackupService->export(
            $performedBy,
            (string) $request->validated('format'),
        );

        return response($file['body'], 200, [
            'Content-Type' => $file['content_type'],
            'Content-Disposition' => 'attachment; filename="'.$file['filename'].'"',
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * POST /api/v1/database-backups/restore
     */
    public function restore(RestoreDatabaseBackupRequest $request): JsonResponse
    {
        $this->authorize('restore', DatabaseBackup::class);

        /** @var User $performedBy */
        $performedBy = $request->user();
        $path = $request->file('backup')?->getRealPath();
        $contents = is_string($path) ? file_get_contents($path) : false;

        if ($contents === false) {
            throw ValidationException::withMessages([
                'backup' => 'The backup file could not be read.',
            ]);
        }

        $this->databaseBackupService->restore($performedBy, $contents);

        return response()->json([
            'message' => 'Database restored from the selected backup. Sign in again if your account is not in that backup.',
        ]);
    }
}
