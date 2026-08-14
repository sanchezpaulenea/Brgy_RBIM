<?php

namespace App\Http\Controllers\Lookup;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lookup\StorePersonnelPositionRequest;
use App\Models\Lookup\Lookup;
use App\Services\Lookup\LookupService;
use Illuminate\Http\JsonResponse;

class LookupController extends Controller
{
    public function __construct(
        protected LookupService $lookupService
    ) {}

    /**
     * List all records for a lookup type.
     *
     * GET /api/v1/lookups/{type}
     */
    public function index(string $type): JsonResponse
    {
        $this->authorize('viewAny', Lookup::class);

        return response()->json([
            'data' => $this->lookupService->getAll($type),
        ]);
    }

    /**
     * Create a personnel position lookup record.
     *
     * POST /api/v1/lookups/{type}
     */
    public function store(StorePersonnelPositionRequest $request, string $type): JsonResponse
    {
        $this->authorize('create', [Lookup::class, $type]);

        $position = $this->lookupService->create($type, $request->validated());

        return response()->json([
            'message' => 'Position created successfully.',
            'data' => $position,
        ], 201);
    }

    /**
     * Delete a personnel position lookup record.
     *
     * DELETE /api/v1/lookups/{type}/{id}
     */
    public function destroy(string $type, int $id): JsonResponse
    {
        $this->authorize('delete', [Lookup::class, $type]);

        $this->lookupService->delete($type, $id);

        return response()->json([
            'message' => 'Position deleted successfully.',
        ]);
    }
}