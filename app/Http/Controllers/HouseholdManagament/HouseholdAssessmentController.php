<?php

namespace App\Http\Controllers\HouseholdManagament;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdManagement\StoreHouseholdAssessmentRequest;
use App\Http\Requests\HouseholdManagement\UpdateHouseholdAssessmentStatusRequest;
use App\Models\HouseholdManagement\Household;
use App\Models\HouseholdManagement\HouseholdAssessment;
use App\Models\UserManagement\User;
use App\Services\HouseholdManagement\HouseholdAssessmentServices;
use Illuminate\Http\JsonResponse;

class HouseholdAssessmentController extends Controller
{
    public function __construct(
        protected HouseholdAssessmentServices $householdAssessmentService,
    ) {}

    /**
     * GET /api/v1/household-assessment-options
     */
    public function options(): JsonResponse
    {
        $this->authorize('create', HouseholdAssessment::class);

        return response()->json([
            'personnel' => $this->householdAssessmentService->listPersonnelOptions(),
        ]);
    }

    /**
     * GET /api/v1/household-assessments
     */
    public function indexAll(): JsonResponse
    {
        $this->authorize('viewAny', HouseholdAssessment::class);

        return response()->json([
            'items' => $this->householdAssessmentService->listAllAssessments(),
        ]);
    }

    /**
     * GET /api/v1/households/{household}/assessments
     */
    public function index(Household $household): JsonResponse
    {
        $this->authorize('view', $household);
        $this->authorize('viewAny', HouseholdAssessment::class);

        return response()->json([
            'items' => $this->householdAssessmentService->listAssessments($household),
        ]);
    }

    /**
     * POST /api/v1/households/{household}/assessments
     */
    public function store(StoreHouseholdAssessmentRequest $request, Household $household): JsonResponse
    {
        $this->authorize('view', $household);
        $this->authorize('create', HouseholdAssessment::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->householdAssessmentService->createAssessment(
            $performedBy,
            $household,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Household assessment encoded successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * PATCH /api/v1/household-assessments/{assessment}/status
     */
    public function updateStatus(
        UpdateHouseholdAssessmentStatusRequest $request,
        HouseholdAssessment $assessment,
    ): JsonResponse {
        $this->authorize('updateStatus', $assessment);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->householdAssessmentService->updateStatus(
            $performedBy,
            $assessment,
            (int) $request->validated('census_status_id'),
        );

        return response()->json([
            'message' => 'Household assessment status updated successfully.',
            'item' => $item,
        ]);
    }
}
