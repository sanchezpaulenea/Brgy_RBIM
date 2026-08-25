<?php

namespace App\Http\Controllers\ResidentManagement\Demographic;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Demographics\StoreEthnicityRequest;
use App\Models\ResidentManagement\Demographic\Ethnicity;
use App\Models\UserManagement\User;
use App\Services\ResidentManagement\Demographic\EthnicityServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EthnicityController extends Controller
{
    public function __construct(
        protected EthnicityServices $ethnicityService,
    ) {}

    /**
     * GET /api/v1/ethnicities
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Ethnicity::class);

        return response()->json([
            'items' => $this->ethnicityService->listEthnicities(),
        ]);
    }

    /**
     * POST /api/v1/ethnicities
     */
    public function store(StoreEthnicityRequest $request): JsonResponse
    {
        $this->authorize('create', Ethnicity::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->ethnicityService->createEthnicity(
            $performedBy,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Ethnicity created successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * DELETE /api/v1/ethnicities/{ethnicity}
     */
    public function destroy(Request $request, Ethnicity $ethnicity): JsonResponse
    {
        $this->authorize('delete', $ethnicity);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $this->ethnicityService->deleteEthnicity($performedBy, $ethnicity);

        return response()->json([
            'message' => 'Ethnicity deleted successfully.',
        ]);
    }
}
