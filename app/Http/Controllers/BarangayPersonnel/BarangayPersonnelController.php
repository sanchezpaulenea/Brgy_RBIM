<?php

namespace App\Http\Controllers\BarangayPersonnel;

use App\Http\Controllers\Controller;
use App\Http\Requests\BarangayPersonnel\StorePersonnelPositionRequest;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\UserManagement\User;
use App\Services\BarangayPersonnel\PersonnelPositionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarangayPersonnelController extends Controller
{
    public function __construct(
        protected PersonnelPositionService $personnelPositionService,
    ) {}

    /**
     * GET /api/v1/personnel-positions
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', PersonnelPosition::class);

        return response()->json([
            'items' => $this->personnelPositionService->listPositions(),
        ]);
    }

    /**
     * POST /api/v1/personnel-positions
     */
    public function store(StorePersonnelPositionRequest $request): JsonResponse
    {
        $this->authorize('create', PersonnelPosition::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->personnelPositionService->createPosition(
            $performedBy,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Personnel position created successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * DELETE /api/v1/personnel-positions/{position}
     */
    public function destroy(Request $request, PersonnelPosition $position): JsonResponse
    {
        $this->authorize('delete', $position);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $this->personnelPositionService->deletePosition($performedBy, $position);

        return response()->json([
            'message' => 'Personnel position deleted successfully.',
        ]);
    }
}
