<?php

namespace App\Http\Controllers\HouseholdManagament;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdManagement\StoreStreetRequest;
use App\Models\HouseholdManagement\Street;
use App\Models\UserManagement\User;
use App\Services\HouseholdManagement\StreetServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StreetController extends Controller
{
    public function __construct(
        protected StreetServices $streetService,
    ) {}

    /**
     * GET /api/v1/streets
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Street::class);

        return response()->json([
            'items' => $this->streetService->listStreets(),
        ]);
    }

    /**
     * POST /api/v1/streets
     */
    public function store(StoreStreetRequest $request): JsonResponse
    {
        $this->authorize('create', Street::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->streetService->createStreet(
            $performedBy,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Street created successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * DELETE /api/v1/streets/{street}
     */
    public function destroy(Request $request, Street $street): JsonResponse
    {
        $this->authorize('delete', $street);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $this->streetService->deleteStreet($performedBy, $street);

        return response()->json([
            'message' => 'Street deleted successfully.',
        ]);
    }
}
