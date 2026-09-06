<?php

namespace App\Http\Controllers\ResidentManagement\Sociocivic;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Sociocivic\StoreSociocivicRequest;
use App\Http\Requests\ResidentManagement\Sociocivic\UpdateSociocivicRequest;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Sociocivic\Sociocivic;
use App\Models\UserManagement\User;
use App\Services\ResidentManagement\Sociocivic\SociocivicService;
use Illuminate\Http\JsonResponse;

class SociocivicController extends Controller
{
    public function __construct(
        protected SociocivicService $sociocivicService,
    ) {}

    /**
     * POST /api/v1/residents/{resident}/sociocivic
     */
    public function store(StoreSociocivicRequest $request, Resident $resident): JsonResponse
    {
        $this->authorize('view', $resident);
        $this->authorize('create', Sociocivic::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->sociocivicService->create($performedBy, $resident, $request->validated());

        return response()->json([
            'message' => 'Sociocivic record saved successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * PATCH /api/v1/sociocivics/{sociocivic}
     */
    public function update(UpdateSociocivicRequest $request, Sociocivic $sociocivic): JsonResponse
    {
        $this->authorize('update', $sociocivic);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->sociocivicService->update($performedBy, $sociocivic, $request->validated());

        return response()->json([
            'message' => 'Sociocivic record updated successfully.',
            'item' => $item,
        ]);
    }
}
