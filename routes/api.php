<?php

use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\Authentication\PasswordController;
use App\Http\Controllers\BarangayPersonnel\BarangayPersonnelController;
use App\Http\Controllers\BarangayPersonnel\PersonnelController;
use App\Http\Controllers\HouseholdManagament\HouseholdController;
use App\Http\Controllers\HouseholdManagament\StreetController;
use App\Http\Controllers\Logs\AuditLogController;
use App\Http\Controllers\Logs\UserLogController;
use App\Http\Controllers\Lookups\LookupController;
use App\Http\Controllers\ResidentManagement\Demographic\EthnicityController;
use App\Http\Controllers\ResidentManagement\Demographic\NationalityController;
use App\Http\Controllers\ResidentManagement\Demographic\ReligionController;
use App\Http\Controllers\ResidentManagement\Demographic\ResidentController;
use App\Http\Controllers\SystemSetting\SystemSettingController;
use App\Http\Controllers\UserManagement\RolePermissionController;
use App\Http\Controllers\UserManagement\UserController;
use App\Http\Controllers\UserManagement\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('auth.login');

        Route::middleware(['auth:sanctum', 'session.timeout'])->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::get('me', [AuthController::class, 'me'])->name('auth.me');
            Route::get('password/policy', [PasswordController::class, 'policy'])->name('auth.password.policy');
            Route::post('password/change', [PasswordController::class, 'change'])->name('auth.password.change');
        });
    });

    Route::middleware(['auth:sanctum', 'session.timeout'])->group(function () {
        Route::get('personnel-positions', [BarangayPersonnelController::class, 'index'])->name('personnel-positions.index');
        Route::get('households', [HouseholdController::class, 'index'])->name('households.index');
        Route::post('households', [HouseholdController::class, 'store'])->name('households.store');
        Route::get('households/{household}', [HouseholdController::class, 'show'])->name('households.show');
        Route::patch('households/{household}', [HouseholdController::class, 'update'])->name('households.update');

        Route::get('residents', [ResidentController::class, 'index'])->name('residents.index');
        Route::post('residents', [ResidentController::class, 'store'])->name('residents.store');
        Route::get('residents/{resident}', [ResidentController::class, 'show'])->name('residents.show');
        Route::patch('residents/{resident}', [ResidentController::class, 'update'])->name('residents.update');

        Route::get('streets', [StreetController::class, 'index'])->name('streets.index');
        Route::post('streets', [StreetController::class, 'store'])->name('streets.store');
        Route::get('nationalities', [NationalityController::class, 'index'])->name('nationalities.index');
        Route::post('nationalities', [NationalityController::class, 'store'])->name('nationalities.store');
        Route::get('ethnicities', [EthnicityController::class, 'index'])->name('ethnicities.index');
        Route::post('ethnicities', [EthnicityController::class, 'store'])->name('ethnicities.store');
        Route::get('religions', [ReligionController::class, 'index'])->name('religions.index');
        Route::post('religions', [ReligionController::class, 'store'])->name('religions.store');

        Route::get('lookups/{lookup}', [LookupController::class, 'index'])->name('lookups.index');
        Route::get('clans', [LookupController::class, 'index'])->defaults('lookup', 'clan')->name('clans.index');
        Route::get('household-statuses', [LookupController::class, 'index'])->defaults('lookup', 'household-status')->name('household-statuses.index');
        Route::get('census-statuses', [LookupController::class, 'index'])->defaults('lookup', 'census-status')->name('census-statuses.index');
        Route::get('marital-statuses', [LookupController::class, 'index'])->defaults('lookup', 'marital-status')->name('marital-statuses.index');
        Route::get('relationships-to-hh', [LookupController::class, 'index'])->defaults('lookup', 'relationship-to-hh')->name('relationships-to-hh.index');
        Route::get('resident-statuses', [LookupController::class, 'index'])->defaults('lookup', 'resident-status')->name('resident-statuses.index');
        Route::get('resident-types', [LookupController::class, 'index'])->defaults('lookup', 'resident-type')->name('resident-types.index');
        Route::get('sexes', [LookupController::class, 'index'])->defaults('lookup', 'sex')->name('sexes.index');

        Route::middleware('system.admin')->group(function () {
            Route::get('users/create-options', [UserController::class, 'createOptions'])->name('users.create-options');
            Route::get('user-statuses', [UserController::class, 'statusOptions'])->name('users.status-options');
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.update-status');
            Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

            Route::get('users/{user}/roles', [UserRoleController::class, 'index'])->name('users.roles.index');
            Route::post('users/{user}/roles', [UserRoleController::class, 'store'])->name('users.roles.store');
            Route::patch('user-roles/{userRole}/status', [UserRoleController::class, 'updateStatus'])->name('user-roles.update-status');
            Route::get('role-permissions', [RolePermissionController::class, 'index'])->name('role-permissions.index');

            Route::get('barangay-personnel', [PersonnelController::class, 'index'])->name('barangay-personnel.index');
            Route::post('barangay-personnel', [PersonnelController::class, 'store'])->name('barangay-personnel.store');
            Route::patch('barangay-personnel/{personnel}', [PersonnelController::class, 'update'])->name('barangay-personnel.update');

            Route::post('personnel-positions', [BarangayPersonnelController::class, 'store'])->name('personnel-positions.store');
            Route::delete('personnel-positions/{position}', [BarangayPersonnelController::class, 'destroy'])->name('personnel-positions.destroy');

            Route::delete('streets/{street}', [StreetController::class, 'destroy'])->name('streets.destroy');
            Route::delete('nationalities/{nationality}', [NationalityController::class, 'destroy'])->name('nationalities.destroy');
            Route::delete('ethnicities/{ethnicity}', [EthnicityController::class, 'destroy'])->name('ethnicities.destroy');
            Route::delete('religions/{religion}', [ReligionController::class, 'destroy'])->name('religions.destroy');

            Route::get('settings', [SystemSettingController::class, 'index'])->name('settings.index');
            Route::patch('settings/{setting}', [SystemSettingController::class, 'update'])->name('settings.update');

            Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
            Route::get('user-logs', [UserLogController::class, 'index'])->name('user-logs.index');
        });
    });
});
