<?php

namespace App\Http\Controllers\ResidentManagement\Health;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Health\StoreDisabilityRequest;
use App\Models\ResidentManagement\Health\Disability;
use Illuminate\Http\JsonResponse;

class DisabilityController extends Controller
{
    /**
     * POST /api/v1/disabilities
     *
     * Reuses an existing disability row when the label already exists.
     */
    public function store(StoreDisabilityRequest $request): JsonResponse
    {
        $this->authorize('create', Disability::class);

        $disability = Disability::findOrCreateByLabel((string) $request->validated('disability'));

        return response()->json([
            'message' => $disability->wasRecentlyCreated
                ? 'Disability created successfully.'
                : 'Existing disability reused.',
            'item' => [
                'id' => $disability->disability_id,
                'label' => $disability->disability,
                'disability_id' => $disability->disability_id,
                'disability' => $disability->disability,
            ],
        ], $disability->wasRecentlyCreated ? 201 : 200);
    }
}
