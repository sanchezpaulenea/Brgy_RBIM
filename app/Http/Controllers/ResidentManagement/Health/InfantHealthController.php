<?php

namespace App\Http\Controllers\ResidentManagement\Health;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Health\StoreInfantHealthRequest;
use App\Http\Requests\ResidentManagement\Health\UpdateInfantHealthRequest;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Health\InfantHealth;
use App\Models\UserManagement\User;
use App\Services\ResidentManagement\Health\InfantHealthService;
use Illuminate\Http\JsonResponse;

class InfantHealthController extends Controller
{
    public function __construct(
        protected InfantHealthService $infantHealthService,
    ) {}

    /**
     * POST /api/v1/residents/{resident}/infant-health
     */
    public function store(StoreInfantHealthRequest $request, Resident $resident): JsonResponse
    {
        $this->authorize('view', $resident);
        $this->authorize('create', InfantHealth::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->infantHealthService->create($performedBy, $resident, $request->validated());

        return response()->json([
            'message' => 'Infant health record saved successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * PATCH /api/v1/infant-health/{infantHealth}
     */
    public function update(UpdateInfantHealthRequest $request, InfantHealth $infantHealth): JsonResponse
    {
        $this->authorize('update', $infantHealth);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->infantHealthService->update($performedBy, $infantHealth, $request->validated());

        return response()->json([
            'message' => 'Infant health record updated successfully.',
            'item' => $item,
        ]);
    }
}
