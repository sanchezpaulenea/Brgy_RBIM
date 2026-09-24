<?php

namespace App\Http\Controllers\ResidentManagement\Skill;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Skill\StoreSkillTypeRequest;
use App\Models\ResidentManagement\Skill\SkillType;
use Illuminate\Http\JsonResponse;

class SkillTypeController extends Controller
{
    public function store(StoreSkillTypeRequest $request): JsonResponse
    {
        $this->authorize('create', SkillType::class);

        $label = (string) ($request->validated('skill_type') ?? $request->validated('label'));
        $skillType = SkillType::findOrCreateByLabel($label);

        return response()->json([
            'message' => $skillType->wasRecentlyCreated
                ? 'Skill type created successfully.'
                : 'Existing skill type reused.',
            'item' => [
                'id' => $skillType->skill_type_id,
                'label' => $skillType->skill_type,
                'skill_type_id' => $skillType->skill_type_id,
                'skill_type' => $skillType->skill_type,
            ],
        ], $skillType->wasRecentlyCreated ? 201 : 200);
    }
}
