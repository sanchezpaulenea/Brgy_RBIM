<?php

namespace App\Providers;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\HouseholdManagement\CensusStatus;
use App\Models\HouseholdManagement\Clan;
use App\Models\HouseholdManagement\Household;
use App\Models\HouseholdManagement\HouseholdStatus;
use App\Models\HouseholdManagement\Street;
use App\Models\Logs\AuditLog;
use App\Models\Logs\UserLog;
use App\Models\ResidentManagement\Demographic\Ethnicity;
use App\Models\ResidentManagement\Demographic\MaritalStatus;
use App\Models\ResidentManagement\Demographic\Nationality;
use App\Models\ResidentManagement\Demographic\RelationshipToHouseholdHead;
use App\Models\ResidentManagement\Demographic\Religion;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Demographic\ResidentStatus;
use App\Models\ResidentManagement\Demographic\ResidentType;
use App\Models\ResidentManagement\Demographic\Sex;
use App\Models\Setting\Setting;
use App\Models\UserManagement\User;
use App\Models\UserManagement\UserRole;
use App\Policies\BarangayPersonnel\BarangayPersonnelPolicy;
use App\Policies\BarangayPersonnel\PersonnelPolicy;
use App\Policies\HouseholdManagement\HouseholdPolicy;
use App\Policies\HouseholdManagement\StreetPolicy;
use App\Policies\Logs\AuditLogPolicy;
use App\Policies\Logs\UserLogPolicy;
use App\Policies\Lookups\ReferenceLookupPolicy;
use App\Policies\ResidentManagement\Demographic\EthnicityPolicy;
use App\Policies\ResidentManagement\Demographic\NationalityPolicy;
use App\Policies\ResidentManagement\Demographic\ReligionPolicy;
use App\Policies\ResidentManagement\Demographic\ResidentPolicy;
use App\Policies\SystemSetting\SystemSettingPolicy;
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
        PersonnelPosition::class => BarangayPersonnelPolicy::class,
        BarangayPersonnel::class => PersonnelPolicy::class,
        Setting::class => SystemSettingPolicy::class,
        Household::class => HouseholdPolicy::class,
        Street::class => StreetPolicy::class,
        Resident::class => ResidentPolicy::class,
        Nationality::class => NationalityPolicy::class,
        Ethnicity::class => EthnicityPolicy::class,
        Religion::class => ReligionPolicy::class,
        CensusStatus::class => ReferenceLookupPolicy::class,
        Clan::class => ReferenceLookupPolicy::class,
        HouseholdStatus::class => ReferenceLookupPolicy::class,
        MaritalStatus::class => ReferenceLookupPolicy::class,
        RelationshipToHouseholdHead::class => ReferenceLookupPolicy::class,
        ResidentStatus::class => ReferenceLookupPolicy::class,
        ResidentType::class => ReferenceLookupPolicy::class,
        Sex::class => ReferenceLookupPolicy::class,
    ];

    public function register(): void {}

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
