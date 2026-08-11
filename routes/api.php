<?php

use App\Http\Controllers\Authentication\LoginController;
use App\Http\Controllers\Authentication\LogoutController;
use App\Http\Controllers\Authentication\MeController;
use App\Http\Controllers\Authentication\PasswordController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
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
});
