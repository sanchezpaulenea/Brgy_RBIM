<?php

use App\Http\Controllers\AuditLog\AuditLogController;
use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\Authentication\PasswordController;
use App\Http\Controllers\Lookup\LookupController;
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\UserManagement\UserController;
use App\Http\Controllers\UserManagement\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('auth.login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::get('me', [AuthController::class, 'me'])->name('auth.me');
            Route::post('password/change', [PasswordController::class, 'change'])->name('auth.password.change');

            Route::middleware('system.admin')->group(function () {
                Route::get('logs', [AuthController::class, 'userLogs'])->name('auth.logs');
            });
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('lookups/{type}', [LookupController::class, 'index'])->name('lookups.index');

        Route::middleware('system.admin')->group(function () {
            Route::get('users/create-options', [UserController::class, 'createOptions'])->name('users.create-options');
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.update-status');
            Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

            Route::get('users/{user}/roles', [UserRoleController::class, 'index'])->name('users.roles.index');
            Route::post('users/{user}/roles', [UserRoleController::class, 'store'])->name('users.roles.store');
            Route::patch('user-roles/{userRole}/status', [UserRoleController::class, 'updateStatus'])->name('user-roles.update-status');

            Route::post('lookups/{type}', [LookupController::class, 'store'])->name('lookups.store');
            Route::delete('lookups/{type}/{id}', [LookupController::class, 'destroy'])->name('lookups.destroy');

            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::patch('settings/{setting}', [SettingController::class, 'update'])->name('settings.update');

            Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        });
    });
});
