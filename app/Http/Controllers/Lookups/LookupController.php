<?php

namespace App\Http\Controllers\Lookups;

use App\Enums\LookupType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Lookups\ListMunicipalitiesRequest;
use App\Http\Requests\Lookups\SearchBarangayRequest;
use App\Http\Requests\Lookups\StoreBarangayRequest;
use App\Http\Requests\Lookups\StoreMunicipalityCityRequest;
use App\Http\Requests\Lookups\StoreProvinceRequest;
use App\Models\Geographic\Barangay;
use App\Models\Geographic\MunicipalityCity;
use App\Models\Geographic\Province;
use App\Models\UserManagement\User;
use App\Services\Lookups\LookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function __construct(
        protected LookupService $lookupService,
    ) {}

    /**
     * GET /api/v1/lookups/{lookup}
     * GET /api/v1/clans and the other seeded reference aliases
     */
    public function index(Request $request, ?string $lookup = null): JsonResponse
    {
        $slug = $lookup ?: (string) $request->route('lookup');
        $type = LookupType::fromSlug($slug);

        $this->authorize('viewAny', $type->modelClass());

        return response()->json([
            'items' => $this->lookupService->list($type),
        ]);
    }

    /**
     * GET /api/v1/lookups/barangays?q=
     */
    public function searchBarangays(SearchBarangayRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Barangay::class);

        $municipalityId = $request->validated('municipality_city_id');

        return response()->json([
            'items' => $this->lookupService->searchBarangays(
                (string) ($request->validated('q') ?? ''),
                is_numeric($municipalityId) ? (int) $municipalityId : null,
            ),
        ]);
    }

    public function provinces(): JsonResponse
    {
        $this->authorize('viewAny', Province::class);

        return response()->json([
            'items' => $this->lookupService->provinces(),
        ]);
    }

    public function municipalities(ListMunicipalitiesRequest $request): JsonResponse
    {
        $this->authorize('viewAny', MunicipalityCity::class);

        return response()->json([
            'items' => $this->lookupService->municipalities((int) $request->validated('province_id')),
        ]);
    }

    public function storeProvince(StoreProvinceRequest $request): JsonResponse
    {
        $this->authorize('create', Province::class);

        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'item' => $this->lookupService->createProvince($user, $request->validated()),
        ], 201);
    }

    public function storeMunicipality(StoreMunicipalityCityRequest $request): JsonResponse
    {
        $this->authorize('create', MunicipalityCity::class);

        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'item' => $this->lookupService->createMunicipality($user, $request->validated()),
        ], 201);
    }

    public function storeBarangay(StoreBarangayRequest $request): JsonResponse
    {
        $this->authorize('create', Barangay::class);

        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'item' => $this->lookupService->createBarangay($user, $request->validated()),
        ], 201);
    }
}
