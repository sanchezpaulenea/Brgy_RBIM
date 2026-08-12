<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateSettingRequest;
use App\Models\Setting\Setting;
use App\Models\UserManagement\User;
use App\Services\Setting\SettingService;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService,
    ) {}

    /**
     * GET /api/v1/settings
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        return response()->json([
            'settings' => $this->settingService->listSettings(),
        ]);
    }

    /**
     * PATCH /api/v1/settings/{setting}
     */
    public function update(UpdateSettingRequest $request, Setting $setting): JsonResponse
    {
        $this->authorize('update', $setting);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $updated = $this->settingService->updateSetting(
            $performedBy,
            $setting,
            $request->normalizedValue(),
        );

        return response()->json([
            'message' => 'Setting updated successfully.',
            'setting' => $updated,
        ]);
    }
}
