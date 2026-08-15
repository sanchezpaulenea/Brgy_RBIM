<?php

namespace App\Providers;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\Logs\AuditLog;
use App\Models\Logs\UserLog;
use App\Models\Setting\Setting;
use App\Models\UserManagement\User;
use App\Models\UserManagement\UserRole;
use App\Policies\BarangayPersonnel\BarangayPersonnelPolicy;
use App\Policies\BarangayPersonnel\PersonnelPolicy;
use App\Policies\Logs\AuditLogPolicy;
use App\Policies\Logs\UserLogPolicy;
use App\Policies\SystemSetting\SystemSettingPolicy;
use App\Policies\UserManagement\UserPolicy;
use App\Policies\UserManagement\UserRolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Registers Laravel Policies by binding each Model to its Policy class.
 * This provider is registered in bootstrap/app.php.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        User::class => UserPolicy::class,
        UserRole::class => UserRolePolicy::class,
        UserLog::class => UserLogPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        PersonnelPosition::class => BarangayPersonnelPolicy::class,
        BarangayPersonnel::class => PersonnelPolicy::class,
        Setting::class => SystemSettingPolicy::class,
    ];

    public function register(): void {}

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
