<?php

namespace App\Http\Controllers\ResidentManagement\Demographic;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Demographics\StoreNationalityRequest;
use App\Models\ResidentManagement\Demographic\Nationality;
use App\Models\UserManagement\User;
use App\Services\ResidentManagement\Demographic\NationalityServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NationalityController extends Controller
{
    public function __construct(
        protected NationalityServices $nationalityService,
    ) {}

    /**
     * GET /api/v1/nationalities
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Nationality::class);

        return response()->json([
            'items' => $this->nationalityService->listNationalities(),
        ]);
    }

    /**
     * POST /api/v1/nationalities
     */
    public function store(StoreNationalityRequest $request): JsonResponse
    {
        $this->authorize('create', Nationality::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->nationalityService->createNationality(
            $performedBy,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Nationality created successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * DELETE /api/v1/nationalities/{nationality}
     */
    public function destroy(Request $request, Nationality $nationality): JsonResponse
    {
        $this->authorize('delete', $nationality);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $this->nationalityService->deleteNationality($performedBy, $nationality);

        return response()->json([
            'message' => 'Nationality deleted successfully.',
        ]);
    }
}
