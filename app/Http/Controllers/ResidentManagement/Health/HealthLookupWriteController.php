<?php

namespace App\Http\Controllers\ResidentManagement\Health;

use App\Enums\LookupType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentManagement\Health\StoreHealthLookupRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

class HealthLookupWriteController extends Controller
{
    /**
     * POST /api/v1/{health-lookup}
     *
     * Reuses an existing row when the label already exists, matching Disability.
     */
    public function store(StoreHealthLookupRequest $request, ?string $lookup = null): JsonResponse
    {
        $type = LookupType::fromSlug($lookup ?: (string) $request->route('lookup'));
        $modelClass = $type->modelClass();

        $this->authorize('create', $modelClass);

        $labelColumn = $type->labelColumn();
        $label = (string) ($request->validated()[$labelColumn] ?? $request->validated('label') ?? '');

        /** @var class-string<Model> $modelClass */
        $item = $modelClass::findOrCreateByLabel($label);

        return response()->json([
            'message' => $item->wasRecentlyCreated
                ? ucfirst(str_replace('-', ' ', $type->value)).' created successfully.'
                : 'Existing value reused.',
            'item' => $type->format($item),
        ], $item->wasRecentlyCreated ? 201 : 200);
    }
}
