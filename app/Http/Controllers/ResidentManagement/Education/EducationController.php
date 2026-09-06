<?php

namespace App\Http\Controllers\ResidentManagement\Education;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Education\StoreEducationRequest;
use App\Http\Requests\ResidentManagement\Education\UpdateEducationRequest;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Education\Education;
use App\Models\UserManagement\User;
use App\Services\ResidentManagement\Education\EducationService;
use Illuminate\Http\JsonResponse;

class EducationController extends Controller
{
    public function __construct(
        protected EducationService $educationService,
    ) {}

    /**
     * POST /api/v1/residents/{resident}/education
     */
    public function store(StoreEducationRequest $request, Resident $resident): JsonResponse
    {
        $this->authorize('view', $resident);
        $this->authorize('create', Education::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->educationService->create($performedBy, $resident, $request->validated());

        return response()->json([
            'message' => 'Education record saved successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * PATCH /api/v1/educations/{education}
     */
    public function update(UpdateEducationRequest $request, Education $education): JsonResponse
    {
        $this->authorize('update', $education);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->educationService->update($performedBy, $education, $request->validated());

        return response()->json([
            'message' => 'Education record updated successfully.',
            'item' => $item,
        ]);
    }
}
