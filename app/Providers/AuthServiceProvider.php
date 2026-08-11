<?php

namespace App\Providers;

use App\Models\UserManagement\User;
use App\Policies\UserManagement\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Registers Laravel Policies by binding each Model to its Policy class.
 * This provider is registered in bootstrap/app.php.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Boot policy registrations.
     *
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        User::class => UserPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap auth/policy services.
     */
    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
