<?php

use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\Authentication\PasswordController;
use App\Http\Controllers\BarangayPersonnel\BarangayPersonnelController;
use App\Http\Controllers\BarangayPersonnel\PersonnelController;
use App\Http\Controllers\HouseholdManagament\HouseholdAssessmentController;
use App\Http\Controllers\HouseholdManagament\HouseholdController;
use App\Http\Controllers\HouseholdManagament\StreetController;
use App\Http\Controllers\Logs\AuditLogController;
use App\Http\Controllers\Logs\UserLogController;
use App\Http\Controllers\Lookups\LookupController;
use App\Http\Controllers\ResidentManagement\Ctc\CtcController;
use App\Http\Controllers\ResidentManagement\Demographic\EthnicityController;
use App\Http\Controllers\ResidentManagement\Demographic\NationalityController;
use App\Http\Controllers\ResidentManagement\Demographic\ReligionController;
use App\Http\Controllers\ResidentManagement\Demographic\ResidentController;
use App\Http\Controllers\ResidentManagement\Economic\EconomicController;
use App\Http\Controllers\ResidentManagement\Education\EducationController;
use App\Http\Controllers\ResidentManagement\Health\HealthController;
use App\Http\Controllers\ResidentManagement\Health\InfantHealthController;
use App\Http\Controllers\ResidentManagement\Health\WomenHealthController;
use App\Http\Controllers\ResidentManagement\Migration\MigrationController;
use App\Http\Controllers\ResidentManagement\Skill\SkillController;
use App\Http\Controllers\ResidentManagement\Sociocivic\SociocivicController;
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
            Route::post('profile/avatar', [AuthController::class, 'updateAvatar'])->name('auth.profile.avatar.update');
            Route::get('profile/avatar', [AuthController::class, 'avatar'])->name('auth.profile.avatar.show');
            Route::get('password/policy', [PasswordController::class, 'policy'])->name('auth.password.policy');
            Route::post('password/change', [PasswordController::class, 'change'])->name('auth.password.change');
        });
    });

    Route::middleware(['auth:sanctum', 'session.timeout'])->group(function () {
        Route::get('personnel-positions', [BarangayPersonnelController::class, 'index'])->name('personnel-positions.index');
        Route::get('households', [HouseholdController::class, 'index'])->name('households.index');
        Route::post('households', [HouseholdController::class, 'store'])->name('households.store');
        Route::get('household-assessment-options', [HouseholdAssessmentController::class, 'options'])->name('household-assessments.options');
        Route::get('household-assessments', [HouseholdAssessmentController::class, 'indexAll'])->name('household-assessments.index');
        Route::get('households/{household}/assessments', [HouseholdAssessmentController::class, 'index'])->name('households.assessments.index');
        Route::post('households/{household}/assessments', [HouseholdAssessmentController::class, 'store'])->name('households.assessments.store');
        Route::patch('household-assessments/{assessment}/status', [HouseholdAssessmentController::class, 'updateStatus'])->name('household-assessments.update-status');
        Route::get('households/{household}', [HouseholdController::class, 'show'])->name('households.show');
        Route::patch('households/{household}', [HouseholdController::class, 'update'])->name('households.update');

        Route::get('residents', [ResidentController::class, 'index'])->name('residents.index');
        Route::post('residents', [ResidentController::class, 'store'])->name('residents.store');
        Route::get('residents/{resident}', [ResidentController::class, 'show'])->name('residents.show');
        Route::patch('residents/{resident}', [ResidentController::class, 'update'])->name('residents.update');

        Route::post('residents/{resident}/education', [EducationController::class, 'store'])->name('residents.education.store');
        Route::patch('educations/{education}', [EducationController::class, 'update'])->name('educations.update');
        Route::post('residents/{resident}/economic', [EconomicController::class, 'store'])->name('residents.economic.store');
        Route::patch('economics/{economic}', [EconomicController::class, 'update'])->name('economics.update');
        Route::post('residents/{resident}/infant-health', [InfantHealthController::class, 'store'])->name('residents.infant-health.store');
        Route::patch('infant-health/{infantHealth}', [InfantHealthController::class, 'update'])->name('infant-health.update');
        Route::post('residents/{resident}/health', [HealthController::class, 'store'])->name('residents.health.store');
        Route::patch('health-records/{health}', [HealthController::class, 'update'])->name('health-records.update');
        Route::post('residents/{resident}/women-health', [WomenHealthController::class, 'store'])->name('residents.women-health.store');
        Route::patch('women-health/{womenHealth}', [WomenHealthController::class, 'update'])->name('women-health.update');
        Route::post('residents/{resident}/sociocivic', [SociocivicController::class, 'store'])->name('residents.sociocivic.store');
        Route::patch('sociocivics/{sociocivic}', [SociocivicController::class, 'update'])->name('sociocivics.update');
        Route::post('residents/{resident}/migration', [MigrationController::class, 'store'])->name('residents.migration.store');
        Route::patch('migrations/{migration}', [MigrationController::class, 'update'])->name('migrations.update');
        Route::post('residents/{resident}/ctc', [CtcController::class, 'store'])->name('residents.ctc.store');
        Route::patch('ctcs/{ctc}', [CtcController::class, 'update'])->name('ctcs.update');
        Route::post('residents/{resident}/skills', [SkillController::class, 'store'])->name('residents.skills.store');
        Route::patch('skills/{skillsDevelopment}', [SkillController::class, 'update'])->name('skills.update');

        Route::get('streets', [StreetController::class, 'index'])->name('streets.index');
        Route::post('streets', [StreetController::class, 'store'])->name('streets.store');
        Route::get('nationalities', [NationalityController::class, 'index'])->name('nationalities.index');
        Route::post('nationalities', [NationalityController::class, 'store'])->name('nationalities.store');
        Route::get('ethnicities', [EthnicityController::class, 'index'])->name('ethnicities.index');
        Route::post('ethnicities', [EthnicityController::class, 'store'])->name('ethnicities.store');
        Route::get('religions', [ReligionController::class, 'index'])->name('religions.index');
        Route::post('religions', [ReligionController::class, 'store'])->name('religions.store');

        Route::get('location-profile', [SystemSettingController::class, 'locationProfile'])->name('location-profile.show');
        Route::get('lookups/{lookup}', [LookupController::class, 'index'])->name('lookups.index');
        Route::get('clans', [LookupController::class, 'index'])->defaults('lookup', 'clan')->name('clans.index');
        Route::get('household-statuses', [LookupController::class, 'index'])->defaults('lookup', 'household-status')->name('household-statuses.index');
        Route::get('census-statuses', [LookupController::class, 'index'])->defaults('lookup', 'census-status')->name('census-statuses.index');
        Route::get('marital-statuses', [LookupController::class, 'index'])->defaults('lookup', 'marital-status')->name('marital-statuses.index');
        Route::get('relationships-to-hh', [LookupController::class, 'index'])->defaults('lookup', 'relationship-to-hh')->name('relationships-to-hh.index');
        Route::get('resident-statuses', [LookupController::class, 'index'])->defaults('lookup', 'resident-status')->name('resident-statuses.index');
        Route::get('resident-types', [LookupController::class, 'index'])->defaults('lookup', 'resident-type')->name('resident-types.index');
        Route::get('sexes', [LookupController::class, 'index'])->defaults('lookup', 'sex')->name('sexes.index');
        Route::get('highest-lvls-of-educ', [LookupController::class, 'index'])->defaults('lookup', 'highest-lvl-of-educ')->name('highest-lvls-of-educ.index');
        Route::get('current-enrollment-statuses', [LookupController::class, 'index'])->defaults('lookup', 'current-enrollment-status')->name('current-enrollment-statuses.index');
        Route::get('school-lvls', [LookupController::class, 'index'])->defaults('lookup', 'school-lvl')->name('school-lvls.index');
        Route::get('sources-of-income', [LookupController::class, 'index'])->defaults('lookup', 'source-of-income')->name('sources-of-income.index');
        Route::get('statuses-of-work-business', [LookupController::class, 'index'])->defaults('lookup', 'status-of-work-business')->name('statuses-of-work-business.index');
        Route::get('places-of-delivery', [LookupController::class, 'index'])->defaults('lookup', 'place-of-delivery')->name('places-of-delivery.index');
        Route::get('birth-attendants', [LookupController::class, 'index'])->defaults('lookup', 'birth-attendant')->name('birth-attendants.index');
        Route::get('health-insurances', [LookupController::class, 'index'])->defaults('lookup', 'health-insurance')->name('health-insurances.index');
        Route::get('facilities-visited-past-12mos', [LookupController::class, 'index'])->defaults('lookup', 'facility-visited-past-12mos')->name('facilities-visited-past-12mos.index');
        Route::get('facility-visit-reasons', [LookupController::class, 'index'])->defaults('lookup', 'facility-visit-reason')->name('facility-visit-reasons.index');
        Route::get('family-planning-methods', [LookupController::class, 'index'])->defaults('lookup', 'family-planning-method')->name('family-planning-methods.index');
        Route::get('sources-of-fp-method', [LookupController::class, 'index'])->defaults('lookup', 'source-of-fp-method')->name('sources-of-fp-method.index');
        Route::get('solo-parent-statuses', [LookupController::class, 'index'])->defaults('lookup', 'solo-parent-status')->name('solo-parent-statuses.index');
        Route::get('reasons-for-leaving', [LookupController::class, 'index'])->defaults('lookup', 'reason-for-leaving')->name('reasons-for-leaving.index');
        Route::get('reasons-for-transfer', [LookupController::class, 'index'])->defaults('lookup', 'reason-for-transfer')->name('reasons-for-transfer.index');
        Route::get('skill-types', [LookupController::class, 'index'])->defaults('lookup', 'skill-type')->name('skill-types.index');

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
