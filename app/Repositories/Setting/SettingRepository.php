<?php

namespace App\Repositories\Setting;

use App\Models\Setting\Setting;
use App\Repositories\Interfaces\Setting\SettingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SettingRepository implements SettingRepositoryInterface
{
    /**
     * @return Collection<int, Setting>
     */
    public function all(): Collection
    {
        return Setting::query()
            ->orderBy('setting_key')
            ->get();
    }

    public function findById(int $settingId): ?Setting
    {
        return Setting::query()
            ->where('setting_id', $settingId)
            ->first();
    }

    public function findByKey(string $key): ?Setting
    {
        return Setting::query()
            ->where('setting_key', $key)
            ->first();
    }

    public function update(Setting $setting, string $value): Setting
    {
        $setting->setting_value = $value;
        $setting->save();

        return $setting->fresh();
    }
}
