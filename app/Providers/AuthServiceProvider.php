<?php

namespace App\Providers;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\HouseholdManagement\CensusStatus;
use App\Models\HouseholdManagement\Clan;
use App\Models\HouseholdManagement\Household;
use App\Models\HouseholdManagement\HouseholdAssessment;
use App\Models\HouseholdManagement\HouseholdStatus;
use App\Models\HouseholdManagement\Street;
use App\Models\Logs\AuditLog;
use App\Models\Logs\UserLog;
use App\Models\ResidentManagement\Ctc\Ctc;
use App\Models\ResidentManagement\Demographic\Ethnicity;
use App\Models\ResidentManagement\Demographic\MaritalStatus;
use App\Models\ResidentManagement\Demographic\Nationality;
use App\Models\ResidentManagement\Demographic\RelationshipToHouseholdHead;
use App\Models\ResidentManagement\Demographic\Religion;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Demographic\ResidentStatus;
use App\Models\ResidentManagement\Demographic\ResidentType;
use App\Models\ResidentManagement\Demographic\Sex;
use App\Models\ResidentManagement\Economic\Economic;
use App\Models\ResidentManagement\Economic\SourceOfIncome;
use App\Models\ResidentManagement\Economic\StatusOfWorkBusiness;
use App\Models\ResidentManagement\Education\CurrentEnrollmentStatus;
use App\Models\ResidentManagement\Education\Education;
use App\Models\ResidentManagement\Education\HighestLvlOfEduc;
use App\Models\ResidentManagement\Education\SchoolLvl;
use App\Models\ResidentManagement\Health\Disability;
use App\Models\ResidentManagement\Health\BirthAttendant;
use App\Models\ResidentManagement\Health\FacilityVisitedPast12Mos;
use App\Models\ResidentManagement\Health\FacilityVisitReason;
use App\Models\ResidentManagement\Health\FamilyPlanningMethod;
use App\Models\ResidentManagement\Health\Health;
use App\Models\ResidentManagement\Health\HealthInsurance;
use App\Models\ResidentManagement\Health\InfantHealth;
use App\Models\ResidentManagement\Health\PlaceOfDelivery;
use App\Models\ResidentManagement\Health\SourceOfFPMethod;
use App\Models\ResidentManagement\Health\WomenHealth;
use App\Models\ResidentManagement\Migration\Migration;
use App\Models\ResidentManagement\Migration\ReasonForLeaving;
use App\Models\ResidentManagement\Migration\ReasonForTransfer;
use App\Models\ResidentManagement\Skill\SkillsDevelopment;
use App\Models\ResidentManagement\Skill\SkillType;
use App\Models\ResidentManagement\Sociocivic\Sociocivic;
use App\Models\ResidentManagement\Sociocivic\SoloParentStatus;
use App\Models\Setting\Setting;
use App\Models\UserManagement\User;
use App\Models\UserManagement\UserRole;
use App\Policies\BarangayPersonnel\BarangayPersonnelPolicy;
use App\Policies\BarangayPersonnel\PersonnelPolicy;
use App\Policies\HouseholdManagement\HouseholdAssessmentPolicy;
use App\Policies\HouseholdManagement\HouseholdPolicy;
use App\Policies\HouseholdManagement\StreetPolicy;
use App\Policies\Logs\AuditLogPolicy;
use App\Policies\Logs\UserLogPolicy;
use App\Policies\Lookups\ReferenceLookupPolicy;
use App\Policies\ResidentManagement\Ctc\CtcPolicy;
use App\Policies\ResidentManagement\Demographic\EthnicityPolicy;
use App\Policies\ResidentManagement\Demographic\NationalityPolicy;
use App\Policies\ResidentManagement\Demographic\ReligionPolicy;
use App\Policies\ResidentManagement\Demographic\ResidentPolicy;
use App\Policies\ResidentManagement\Economic\EconomicPolicy;
use App\Policies\ResidentManagement\Education\EducationPolicy;
use App\Policies\ResidentManagement\Health\DisabilityPolicy;
use App\Policies\ResidentManagement\Health\HealthPolicy;
use App\Policies\ResidentManagement\Health\InfantHealthPolicy;
use App\Policies\ResidentManagement\Health\WomenHealthPolicy;
use App\Policies\ResidentManagement\Migration\MigrationPolicy;
use App\Policies\ResidentManagement\Skill\SkillPolicy;
use App\Policies\ResidentManagement\Sociocivic\SociocivicPolicy;
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
        HouseholdAssessment::class => HouseholdAssessmentPolicy::class,
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
        Education::class => EducationPolicy::class,
        Economic::class => EconomicPolicy::class,
        InfantHealth::class => InfantHealthPolicy::class,
        Health::class => HealthPolicy::class,
        WomenHealth::class => WomenHealthPolicy::class,
        Sociocivic::class => SociocivicPolicy::class,
        Migration::class => MigrationPolicy::class,
        Ctc::class => CtcPolicy::class,
        SkillsDevelopment::class => SkillPolicy::class,
        HighestLvlOfEduc::class => ReferenceLookupPolicy::class,
        CurrentEnrollmentStatus::class => ReferenceLookupPolicy::class,
        SchoolLvl::class => ReferenceLookupPolicy::class,
        SourceOfIncome::class => ReferenceLookupPolicy::class,
        StatusOfWorkBusiness::class => ReferenceLookupPolicy::class,
        PlaceOfDelivery::class => ReferenceLookupPolicy::class,
        BirthAttendant::class => ReferenceLookupPolicy::class,
        HealthInsurance::class => ReferenceLookupPolicy::class,
        FacilityVisitedPast12Mos::class => ReferenceLookupPolicy::class,
        FacilityVisitReason::class => ReferenceLookupPolicy::class,
        Disability::class => DisabilityPolicy::class,
        FamilyPlanningMethod::class => ReferenceLookupPolicy::class,
        SourceOfFPMethod::class => ReferenceLookupPolicy::class,
        SoloParentStatus::class => ReferenceLookupPolicy::class,
        ReasonForLeaving::class => ReferenceLookupPolicy::class,
        ReasonForTransfer::class => ReferenceLookupPolicy::class,
        SkillType::class => ReferenceLookupPolicy::class,
    ];

    public function register(): void {}

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
