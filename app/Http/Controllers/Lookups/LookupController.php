<?php

namespace App\Http\Controllers\Lookups;

use App\Enums\LookupType;
use App\Http\Controllers\Controller;
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
}
