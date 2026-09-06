<?php

namespace App\Http\Controllers\ResidentManagement\Health;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Health\StoreWomenHealthRequest;
use App\Http\Requests\ResidentManagement\Health\UpdateWomenHealthRequest;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Health\WomenHealth;
use App\Models\UserManagement\User;
use App\Services\ResidentManagement\Health\WomenHealthService;
use Illuminate\Http\JsonResponse;

class WomenHealthController extends Controller
{
    public function __construct(
        protected WomenHealthService $womenHealthService,
    ) {}

    /**
     * POST /api/v1/residents/{resident}/women-health
     */
    public function store(StoreWomenHealthRequest $request, Resident $resident): JsonResponse
    {
        $this->authorize('view', $resident);
        $this->authorize('create', WomenHealth::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->womenHealthService->create($performedBy, $resident, $request->validated());

        return response()->json([
            'message' => 'Women\'s health record saved successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * PATCH /api/v1/women-health/{womenHealth}
     */
    public function update(UpdateWomenHealthRequest $request, WomenHealth $womenHealth): JsonResponse
    {
        $this->authorize('update', $womenHealth);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->womenHealthService->update($performedBy, $womenHealth, $request->validated());

        return response()->json([
            'message' => 'Women\'s health record updated successfully.',
            'item' => $item,
        ]);
    }
}
