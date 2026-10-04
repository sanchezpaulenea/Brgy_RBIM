<?php

namespace App\Http\Controllers\HouseholdManagament;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdManagement\IndexPetCensusRequest;
use App\Http\Requests\HouseholdManagement\StorePetCensusRequest;
use App\Http\Requests\HouseholdManagement\UpdatePetCensusRequest;
use App\Models\HouseholdManagement\Household;
use App\Models\HouseholdManagement\PetCensus;
use App\Models\UserManagement\User;
use App\Services\HouseholdManagement\PetCensusService;
use Illuminate\Http\JsonResponse;

class PetCensusController extends Controller
{
    public function __construct(
        protected PetCensusService $petCensusService,
    ) {}

    /**
     * GET /api/v1/pet-census
     */
    public function index(IndexPetCensusRequest $request): JsonResponse
    {
        $this->authorize('viewAny', PetCensus::class);

        $result = $this->petCensusService->list($request->filters());

        return response()->json([
            'items' => $result['items'],
            'options' => $result['options'],
        ]);
    }

    public function store(StorePetCensusRequest $request, Household $household): JsonResponse
    {
        $this->authorize('view', $household);
        $this->authorize('create', PetCensus::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $items = $this->petCensusService->createMany(
            $performedBy,
            $household,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Pet census saved successfully.',
            'items' => $items,
        ], 201);
    }

    public function update(UpdatePetCensusRequest $request, PetCensus $petCensus): JsonResponse
    {
        $this->authorize('update', $petCensus);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->petCensusService->update(
            $performedBy,
            $petCensus,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Pet census updated successfully.',
            'item' => $item,
        ]);
    }
}
