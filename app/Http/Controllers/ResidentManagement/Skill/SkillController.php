<?php

namespace App\Http\Controllers\ResidentManagement\Skill;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Skill\StoreSkillRequest;
use App\Http\Requests\ResidentManagement\Skill\UpdateSkillRequest;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Skill\SkillsDevelopment;
use App\Models\UserManagement\User;
use App\Services\ResidentManagement\Skill\SkillService;
use Illuminate\Http\JsonResponse;

class SkillController extends Controller
{
    public function __construct(
        protected SkillService $skillService,
    ) {}

    /**
     * POST /api/v1/residents/{resident}/skills
     */
    public function store(StoreSkillRequest $request, Resident $resident): JsonResponse
    {
        $this->authorize('view', $resident);
        $this->authorize('create', SkillsDevelopment::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->skillService->create($performedBy, $resident, $request->validated());

        return response()->json([
            'message' => 'Skills development record saved successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * PATCH /api/v1/skills/{skillsDevelopment}
     */
    public function update(UpdateSkillRequest $request, SkillsDevelopment $skillsDevelopment): JsonResponse
    {
        $this->authorize('update', $skillsDevelopment);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->skillService->update($performedBy, $skillsDevelopment, $request->validated());

        return response()->json([
            'message' => 'Skills development record updated successfully.',
            'item' => $item,
        ]);
    }
}
