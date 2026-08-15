<?php

namespace App\Services\SystemSetting;

use App\Models\Logs\Action;
use App\Models\Setting\Setting;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\Setting\SettingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SystemSettingService
{
    public function __construct(
        protected SettingRepositoryInterface $settingRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * Retrieve a typed setting value by key for use elsewhere in the app.
     */
    public function get(string $key): mixed
    {
        $setting = $this->settingRepository->findByKey($key);

        if ($setting === null) {
            return '';
        }

        return $setting->getTypedValue();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listSettings(): array
    {
        return $this->settingRepository
            ->all()
            ->map(fn (Setting $setting) => $this->formatSetting($setting))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function updateSetting(User $performedBy, Setting $setting, string $value): array
    {
        $oldValue = $setting->setting_value;

        return DB::transaction(function () use ($performedBy, $setting, $value, $oldValue) {
            $updated = $this->settingRepository->update($setting, $value);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $updated->setting_id,
                description: 'Update system setting',
                oldValue: $oldValue,
                newValue: $updated->setting_value,
                target: $updated->setting_key,
                entity: 'system_setting',
            );

            return $this->formatSetting($updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatSetting(Setting $setting): array
    {
        return [
            'setting_id' => $setting->setting_id,
            'setting_key' => $setting->setting_key,
            'setting_value' => $setting->setting_value,
            'typed_value' => $setting->getTypedValue(),
            'data_type' => $setting->data_type,
            'description' => $setting->description,
        ];
    }
}
