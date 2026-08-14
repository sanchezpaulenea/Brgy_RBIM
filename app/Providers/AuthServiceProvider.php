<?php

namespace App\Providers;

use App\Models\AuditLog\AuditLog;
use App\Models\Authentication\UserLog;
use App\Models\Lookup\Lookup;
use App\Models\Setting\Setting;
use App\Models\UserManagement\User;
use App\Models\UserManagement\UserRole;
use App\Policies\AuditLog\AuditLogPolicy;
use App\Policies\Authentication\UserLogPolicy;
use App\Policies\Lookup\LookupPolicy;
use App\Policies\Setting\SettingPolicy;
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
        Lookup::class => LookupPolicy::class,
        Setting::class => SettingPolicy::class,
    ];

    public function register(): void {}

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
