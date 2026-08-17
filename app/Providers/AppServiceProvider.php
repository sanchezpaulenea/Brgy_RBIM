<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDatabaseTimezone();
    }

    private function configureDatabaseTimezone(): void
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'], true)) {
            return;
        }

        $timezone = config('app.timezone', 'UTC');
        $offset = now($timezone)->format('P');

        DB::statement("SET time_zone = '{$offset}'");
    }
}
