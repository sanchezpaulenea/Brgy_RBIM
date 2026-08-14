<?php

namespace App\Providers;

use App\Repositories\Authentication\AuthRepository;
use App\Repositories\Interfaces\Authentication\AuthRepositoryInterface;
use App\Repositories\Interfaces\Lookup\LookupRepositoryInterface;
use App\Repositories\Lookup\LookupRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds each repository interface to its concrete implementation.
 * This provider is registered in bootstrap/app.php.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(LookupRepositoryInterface::class, LookupRepository::class);
    }
}
