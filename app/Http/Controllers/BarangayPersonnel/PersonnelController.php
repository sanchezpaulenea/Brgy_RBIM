<?php

namespace App\Http\Controllers\BarangayPersonnel;

use App\Http\Controllers\Controller;
use App\Http\Requests\BarangayPersonnel\StorePersonnelRequest;
use App\Http\Requests\BarangayPersonnel\UpdatePersonnelRequest;
use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\UserManagement\User;
use App\Services\BarangayPersonnel\BarangayPersonnelService;
use Illuminate\Http\JsonResponse;

class PersonnelController extends Controller
{
    public function __construct(
        protected BarangayPersonnelService $personnelService,
    ) {}

    /**
     * GET /api/v1/barangay-personnel
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', BarangayPersonnel::class);

        return response()->json([
            'items' => $this->personnelService->listPersonnel(),
        ]);
    }

    /**
     * POST /api/v1/barangay-personnel
     */
    public function store(StorePersonnelRequest $request): JsonResponse
    {
        $this->authorize('create', BarangayPersonnel::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->personnelService->createPersonnel(
            $performedBy,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Barangay personnel created successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * PATCH /api/v1/barangay-personnel/{personnel}
     */
    public function update(UpdatePersonnelRequest $request, BarangayPersonnel $personnel): JsonResponse
    {
        $this->authorize('update', $personnel);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->personnelService->updatePersonnel(
            $performedBy,
            $personnel,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Barangay personnel updated successfully.',
            'item' => $item,
        ]);
    }
}
