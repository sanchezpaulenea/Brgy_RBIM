<?php

namespace App\Http\Controllers\HouseholdManagament;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdManagement\IndexHouseholdRequest;
use App\Http\Requests\HouseholdManagement\StoreHouseholdRequest;
use App\Http\Requests\HouseholdManagement\UpdateHouseholdRequest;
use App\Models\HouseholdManagement\Household;
use App\Models\UserManagement\User;
use App\Services\HouseholdManagement\HouseholdServices;
use Illuminate\Http\JsonResponse;

class HouseholdController extends Controller
{
    public function __construct(
        protected HouseholdServices $householdService,
    ) {}

    /**
     * GET /api/v1/households
     */
    public function index(IndexHouseholdRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Household::class);

        return response()->json([
            'items' => $this->householdService->listHouseholds($request->filters()),
        ]);
    }

    /**
     * POST /api/v1/households
     */
    public function store(StoreHouseholdRequest $request): JsonResponse
    {
        $this->authorize('create', Household::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->householdService->createHousehold(
            $performedBy,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Household registered successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * GET /api/v1/households/{household}
     */
    public function show(Household $household): JsonResponse
    {
        $this->authorize('view', $household);

        return response()->json([
            'item' => $this->householdService->getHousehold($household),
        ]);
    }

    /**
     * PATCH /api/v1/households/{household}
     */
    public function update(UpdateHouseholdRequest $request, Household $household): JsonResponse
    {
        $this->authorize('update', $household);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->householdService->updateHousehold(
            $performedBy,
            $household,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Household updated successfully.',
            'item' => $item,
        ]);
    }
}
