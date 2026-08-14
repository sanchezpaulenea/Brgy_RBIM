<?php

namespace App\Providers;

use App\Repositories\AuditLog\AuditLogRepository;
use App\Repositories\Authentication\SessionRepository;
use App\Repositories\Authentication\UserLogRepository;
use App\Repositories\Interfaces\AuditLog\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\Authentication\SessionRepositoryInterface;
use App\Repositories\Interfaces\Authentication\UserLogRepositoryInterface;
use App\Repositories\Interfaces\Lookup\LookupRepositoryInterface;
use App\Repositories\Interfaces\Setting\SettingRepositoryInterface;
use App\Repositories\Interfaces\UserManagement\UserRepositoryInterface;
use App\Repositories\Interfaces\UserManagement\UserRoleRepositoryInterface;
use App\Repositories\Lookup\LookupRepository;
use App\Repositories\Setting\SettingRepository;
use App\Repositories\UserManagement\UserRepository;
use App\Repositories\UserManagement\UserRoleRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds each repository interface to its concrete implementation.
 * This provider is registered in bootstrap/app.php.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SessionRepositoryInterface::class, SessionRepository::class);
        $this->app->bind(UserLogRepositoryInterface::class, UserLogRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(AuditLogRepositoryInterface::class, AuditLogRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserRoleRepositoryInterface::class, UserRoleRepository::class);
        $this->app->bind(LookupRepositoryInterface::class, LookupRepository::class);
    }
}
