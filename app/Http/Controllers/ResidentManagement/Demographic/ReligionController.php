<?php

namespace App\Http\Controllers\ResidentManagement\Demographic;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Demographics\StoreReligionRequest;
use App\Models\ResidentManagement\Demographic\Religion;
use App\Models\UserManagement\User;
use App\Services\ResidentManagement\Demographic\ReligionServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReligionController extends Controller
{
    public function __construct(
        protected ReligionServices $religionService,
    ) {}

    /**
     * GET /api/v1/religions
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Religion::class);

        return response()->json([
            'items' => $this->religionService->listReligions(),
        ]);
    }

    /**
     * POST /api/v1/religions
     */
    public function store(StoreReligionRequest $request): JsonResponse
    {
        $this->authorize('create', Religion::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->religionService->createReligion(
            $performedBy,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Religion created successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * DELETE /api/v1/religions/{religion}
     */
    public function destroy(Request $request, Religion $religion): JsonResponse
    {
        $this->authorize('delete', $religion);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $this->religionService->deleteReligion($performedBy, $religion);

        return response()->json([
            'message' => 'Religion deleted successfully.',
        ]);
    }
}
