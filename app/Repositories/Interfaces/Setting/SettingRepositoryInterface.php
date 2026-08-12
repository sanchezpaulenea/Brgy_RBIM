<?php

namespace App\Repositories\Interfaces\Setting;

use App\Models\Setting\Setting;
use Illuminate\Database\Eloquent\Collection;

interface SettingRepositoryInterface
{
    /**
     * @return Collection<int, Setting>
     */
    public function all(): Collection;

    public function findById(int $settingId): ?Setting;

    public function findByKey(string $key): ?Setting;

    public function update(Setting $setting, string $value): Setting;
}
