<?php

namespace App\Repositories\Interfaces\SystemSetting;

interface SystemSettingRepositoryInterface
{
    public function getValue(string $key): string;
}
