<?php

namespace App\Repositories\SystemSetting;

use App\Models\SystemSetting\SystemSetting;
use App\Repositories\Interfaces\SystemSetting\SystemSettingRepositoryInterface;

class SystemSettingRepository implements SystemSettingRepositoryInterface
{
    public function getValue(string $key): string
    {
        $setting = SystemSetting::where('setting_key', $key)->first();

        return $setting?->setting_value ?? '';
    }
}
