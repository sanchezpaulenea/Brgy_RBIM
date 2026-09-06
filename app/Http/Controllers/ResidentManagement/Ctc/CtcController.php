<?php

namespace App\Http\Controllers\ResidentManagement\Ctc;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Ctc\StoreCtcRequest;
use App\Http\Requests\ResidentManagement\Ctc\UpdateCtcRequest;
use App\Models\ResidentManagement\Ctc\Ctc;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\UserManagement\User;
use App\Services\ResidentManagement\Ctc\CtcService;
use Illuminate\Http\JsonResponse;

class CtcController extends Controller
{
    public function __construct(
        protected CtcService $ctcService,
    ) {}

    /**
     * POST /api/v1/residents/{resident}/ctc
     */
    public function store(StoreCtcRequest $request, Resident $resident): JsonResponse
    {
        $this->authorize('view', $resident);
        $this->authorize('create', Ctc::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->ctcService->create($performedBy, $resident, $request->validated());

        return response()->json([
            'message' => 'Community tax certificate saved successfully.',
            'item' => $item,
        ], 201);
    }

    /**
     * PATCH /api/v1/ctcs/{ctc}
     */
    public function update(UpdateCtcRequest $request, Ctc $ctc): JsonResponse
    {
        $this->authorize('update', $ctc);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $item = $this->ctcService->update($performedBy, $ctc, $request->validated());

        return response()->json([
            'message' => 'Community tax certificate updated successfully.',
            'item' => $item,
        ]);
    }
}
