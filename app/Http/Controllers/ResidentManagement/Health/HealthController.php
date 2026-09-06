<?php

namespace App\Http\Controllers\ResidentManagement\Health;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Health\StoreHealthRequest;
use App\Http\Requests\ResidentManagement\Health\UpdateHealthRequest;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Health\Health;
use App\Models\UserManagement\User;
use App\Services\ResidentManagement\Health\HealthService;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __construct(
        protected HealthService $healthService,
    ) {}

    /**
     * POST /api/v1/residents/{resident}/health
     */
    public function store(StoreHealthRequest $request, Resident $resident): JsonResponse
    {
        $this->authorize('view', $resident);
        $this->authorize('create', Health::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->healthService->create($performedBy, $resident, $request->validated());

        return response()->json([
            'message' => 'Health record saved successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * PATCH /api/v1/health-records/{health}
     */
    public function update(UpdateHealthRequest $request, Health $health): JsonResponse
    {
        $this->authorize('update', $health);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->healthService->update($performedBy, $health, $request->validated());

        return response()->json([
            'message' => 'Health record updated successfully.',
            'item' => $item,
        ]);
    }
}
