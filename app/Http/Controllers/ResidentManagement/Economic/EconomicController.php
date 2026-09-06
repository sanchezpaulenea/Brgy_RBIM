<?php

namespace App\Http\Controllers\ResidentManagement\Economic;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Economic\StoreEconomicRequest;
use App\Http\Requests\ResidentManagement\Economic\UpdateEconomicRequest;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Economic\Economic;
use App\Models\UserManagement\User;
use App\Services\ResidentManagement\Economic\EconomicService;
use Illuminate\Http\JsonResponse;

class EconomicController extends Controller
{
    public function __construct(
        protected EconomicService $economicService,
    ) {}

    /**
     * POST /api/v1/residents/{resident}/economic
     */
    public function store(StoreEconomicRequest $request, Resident $resident): JsonResponse
    {
        $this->authorize('view', $resident);
        $this->authorize('create', Economic::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->economicService->create($performedBy, $resident, $request->validated());

        return response()->json([
            'message' => 'Economic record saved successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * PATCH /api/v1/economics/{economic}
     */
    public function update(UpdateEconomicRequest $request, Economic $economic): JsonResponse
    {
        $this->authorize('update', $economic);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->economicService->update($performedBy, $economic, $request->validated());

        return response()->json([
            'message' => 'Economic record updated successfully.',
            'item' => $item,
        ]);
    }
}
