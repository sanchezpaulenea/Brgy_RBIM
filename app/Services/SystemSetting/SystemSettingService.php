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
     * Read-only barangay location used on household encoding forms.
     *
     * @return array{province: string, city: string, barangay: string, address: string}
     */
    public function locationProfile(): array
    {
        $city = trim((string) $this->get('city_name'));
        $barangay = trim((string) $this->get('barangay_name'));
        $address = trim((string) $this->get('barangay_address'));

        return [
            'province' => self::provinceFromAddress($address, $city, $barangay),
            'city' => $city,
            'barangay' => $barangay,
            'address' => $address,
        ];
    }

    /**
     * Province is the last address segment that is not the city or barangay name.
     */
    public static function provinceFromAddress(string $address, string $city = '', string $barangay = ''): string
    {
        $parts = collect(preg_split('/,+/', $address) ?: [])
            ->map(fn (string $part) => trim($part, " \t\n\r\0\x0B,"))
            ->filter()
            ->values();

        if ($parts->isEmpty()) {
            return '';
        }

        $skip = collect([$city, $barangay])
            ->map(fn (string $value) => mb_strtolower(trim($value)))
            ->filter()
            ->all();

        $province = $parts->reverse()->first(
            fn (string $part) => ! in_array(mb_strtolower($part), $skip, true)
        );

        return is_string($province) ? $province : '';
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
