<?php

use App\Http\Controllers\Authentication\LoginController;
use App\Http\Controllers\Authentication\LogoutController;
use App\Http\Controllers\Authentication\MeController;
use App\Http\Controllers\Authentication\PasswordController;
use App\Http\Controllers\Lookup\LookupController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->middleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ValidateCsrfToken::class,
    ])->group(function () {
        /*
         * Public — no authentication required.
         */
        Route::post('login', [LoginController::class, 'login'])->name('auth.login');

        /*
         * Protected — requires a valid Sanctum stateful session.
         */
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [LogoutController::class, 'logout'])->name('auth.logout');
            Route::post('password/change', [PasswordController::class, 'change'])->name('auth.password.change');
            Route::get('me', [MeController::class, 'me'])->name('auth.me');
        });
    });

    Route::prefix('lookups')->middleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ValidateCsrfToken::class,
        'auth:sanctum',
    ])->group(function () {
        Route::get('{type}', [LookupController::class, 'index'])->name('lookups.index');
        Route::post('{type}', [LookupController::class, 'store'])->name('lookups.store');
        Route::delete('{type}/{id}', [LookupController::class, 'destroy'])->name('lookups.destroy');
    });
});
