<?php

namespace App\Http\Controllers\ResidentManagement\Demographic;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Demographics\IndexResidentRequest;
use App\Http\Requests\ResidentManagement\Demographics\StoreResidentRequest;
use App\Http\Requests\ResidentManagement\Demographics\UpdateResidentRequest;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\UserManagement\User;
use App\Services\ResidentManagement\Demographic\ResidentServices;
use Illuminate\Http\JsonResponse;

class ResidentController extends Controller
{
    public function __construct(
        protected ResidentServices $residentService,
    ) {}

    /**
     * GET /api/v1/residents
     */
    public function index(IndexResidentRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Resident::class);

        return response()->json([
            'items' => $this->residentService->listResidents($request->filters()),
        ]);
    }

    /**
     * POST /api/v1/residents
     */
    public function store(StoreResidentRequest $request): JsonResponse
    {
        $this->authorize('create', Resident::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->residentService->createResident(
            $performedBy,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Resident registered successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * GET /api/v1/residents/{resident}
     */
    public function show(Resident $resident): JsonResponse
    {
        $this->authorize('view', $resident);

        return response()->json([
            'item' => $this->residentService->getResident($resident),
        ]);
    }

    /**
     * PATCH /api/v1/residents/{resident}
     */
    public function update(UpdateResidentRequest $request, Resident $resident): JsonResponse
    {
        $this->authorize('update', $resident);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->residentService->updateResident(
            $performedBy,
            $resident,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Resident updated successfully.',
            'item' => $item,
        ]);
    }
}
