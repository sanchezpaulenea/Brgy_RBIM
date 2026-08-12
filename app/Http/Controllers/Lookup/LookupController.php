<?php

namespace App\Http\Controllers\Lookup;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lookup\StoreLookupRequest;
use App\Models\Lookup\Lookup;
use App\Models\UserManagement\User;
use App\Policies\Lookup\LookupPolicy;
use App\Services\Lookup\LookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function __construct(
        protected LookupService $lookupService,
    ) {}

    /**
     * GET /api/v1/lookups/{type}
     */
    public function index(string $type): JsonResponse
    {
        $this->authorize('viewAny', Lookup::class);

        $this->lookupService->assertSupportedType($type);

        return response()->json([
            'type' => $type,
            'items' => $this->lookupService->listLookups($type),
        ]);
    }

    /**
     * POST /api/v1/lookups/{type}
     */
    public function store(StoreLookupRequest $request, string $type): JsonResponse
    {
        $this->lookupService->assertSupportedType($type);

        if ($type !== LookupPolicy::PERSONNEL_POSITION) {
            abort(405, 'This lookup type is read-only.');
        }

        $this->authorize('create', new Lookup($type));

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->lookupService->createLookup(
            $performedBy,
            $type,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Lookup value created successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * DELETE /api/v1/lookups/{type}/{id}
     */
    public function destroy(Request $request, string $type, int $id): JsonResponse
    {
        $this->lookupService->assertSupportedType($type);

        if ($type !== LookupPolicy::PERSONNEL_POSITION) {
            abort(405, 'This lookup type is read-only.');
        }

        $this->authorize('delete', new Lookup($type));

        /** @var User $performedBy */
        $performedBy = $request->user();

        $this->lookupService->deleteLookup($performedBy, $type, $id);

        return response()->json([
            'message' => 'Lookup value deleted successfully.',
        ]);
    }
}
