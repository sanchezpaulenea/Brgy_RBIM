<?php

namespace App\Http\Controllers\HouseholdManagament;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdManagement\StoreHouseholdQuestionsRequest;
use App\Http\Requests\HouseholdManagement\UpdateHouseholdQuestionsRequest;
use App\Models\HouseholdManagement\Household;
use App\Models\HouseholdManagement\HouseholdQuestions;
use App\Models\UserManagement\User;
use App\Services\HouseholdManagement\HouseholdQuestionsService;
use Illuminate\Http\JsonResponse;

class HouseholdQuestionsController extends Controller
{
    public function __construct(
        protected HouseholdQuestionsService $householdQuestionsService,
    ) {}

    /**
     * POST /api/v1/households/{household}/questions
     */
    public function store(StoreHouseholdQuestionsRequest $request, Household $household): JsonResponse
    {
        $this->authorize('view', $household);
        $this->authorize('create', HouseholdQuestions::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->householdQuestionsService->create(
            $performedBy,
            $household,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Household questions saved successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * PATCH /api/v1/household-questions/{householdQuestions}
     */
    public function update(UpdateHouseholdQuestionsRequest $request, HouseholdQuestions $householdQuestions): JsonResponse
    {
        $this->authorize('update', $householdQuestions);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->householdQuestionsService->update(
            $performedBy,
            $householdQuestions,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Household questions updated successfully.',
            'item' => $item,
        ]);
    }
}
