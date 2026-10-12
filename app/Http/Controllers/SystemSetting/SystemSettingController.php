<?php

namespace App\Http\Controllers\SystemSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\SystemSetting\StorePunongBarangaySignatureRequest;
use App\Http\Requests\SystemSetting\UpdateSettingRequest;
use App\Models\Setting\Setting;
use App\Models\UserManagement\User;
use App\Services\SystemSetting\SystemSettingService;
use Illuminate\Http\JsonResponse;

class SystemSettingController extends Controller
{
    public function __construct(
        protected SystemSettingService $settingService,
    ) {}

    /**
     * GET /api/v1/location-profile
     */
    public function locationProfile(): JsonResponse
    {
        $this->authorize('viewLocationProfile', Setting::class);

        return response()->json([
            'location' => $this->settingService->locationProfile(),
        ]);
    }

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

    /**
     * POST /api/v1/settings/punong-barangay-signature
     */
    public function storePunongBarangaySignature(StorePunongBarangaySignatureRequest $request): JsonResponse
    {
        $setting = $this->settingService->findByKey('punong_barangay_signature') ?? new Setting([
            'setting_key' => 'punong_barangay_signature',
        ]);

        $this->authorize('update', $setting);

        /** @var User $performedBy */
        $performedBy = $request->user();

        return response()->json([
            'message' => 'Punong Barangay signature uploaded.',
            'setting' => $this->settingService->uploadPunongBarangaySignature(
                $performedBy,
                $request->file('signature'),
            ),
        ]);
    }

    /**
     * DELETE /api/v1/settings/punong-barangay-signature
     */
    public function destroyPunongBarangaySignature(): JsonResponse
    {
        $setting = $this->settingService->findByKey('punong_barangay_signature') ?? new Setting([
            'setting_key' => 'punong_barangay_signature',
        ]);

        $this->authorize('update', $setting);

        /** @var User $performedBy */
        $performedBy = request()->user();

        return response()->json([
            'message' => 'Punong Barangay signature removed.',
            'setting' => $this->settingService->removePunongBarangaySignature($performedBy),
        ]);
    }
}
