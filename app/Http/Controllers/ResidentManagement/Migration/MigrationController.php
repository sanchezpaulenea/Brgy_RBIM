<?php

namespace App\Http\Controllers\ResidentManagement\Migration;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Migration\StoreMigrationRequest;
use App\Http\Requests\ResidentManagement\Migration\UpdateMigrationRequest;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Migration\Migration;
use App\Models\UserManagement\User;
use App\Services\ResidentManagement\Migration\MigrationService;
use Illuminate\Http\JsonResponse;

class MigrationController extends Controller
{
    public function __construct(
        protected MigrationService $migrationService,
    ) {}

    /**
     * POST /api/v1/residents/{resident}/migration
     */
    public function store(StoreMigrationRequest $request, Resident $resident): JsonResponse
    {
        $this->authorize('view', $resident);
        $this->authorize('create', Migration::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->migrationService->create($performedBy, $resident, $request->validated());

        return response()->json([
            'message' => 'Migration record saved successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * PATCH /api/v1/migrations/{migration}
     */
    public function update(UpdateMigrationRequest $request, Migration $migration): JsonResponse
    {
        $this->authorize('update', $migration);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->migrationService->update($performedBy, $migration, $request->validated());

        return response()->json([
            'message' => 'Migration record updated successfully.',
            'item' => $item,
        ]);
    }
}
