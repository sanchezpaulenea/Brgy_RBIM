<?php

namespace App\Http\Controllers\HouseholdManagament;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdManagement\StoreHouseholdRequest;
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
}
