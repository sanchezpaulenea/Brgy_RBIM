<?php

namespace App\Providers;

use App\Repositories\BarangayPersonnel\BarangayPersonnelRepository;
use App\Repositories\BarangayPersonnel\PersonnelPositionRepository;
use App\Repositories\HouseholdManagement\HouseholdAssessmentRepository;
use App\Repositories\HouseholdManagement\HouseholdRepository;
use App\Repositories\HouseholdManagement\StreetRepository;
use App\Repositories\Interfaces\BarangayPersonnel\BarangayPersonnelRepositoryInterface;
use App\Repositories\Interfaces\BarangayPersonnel\PersonnelPositionRepositoryInterface;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdAssessmentRepositoryInterface;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdRepositoryInterface;
use App\Repositories\Interfaces\HouseholdManagement\StreetRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\Logs\UserLogRepositoryInterface;
use App\Repositories\Interfaces\Lookups\LookupRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Ctc\CtcRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Demographic\EthnicityRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Demographic\NationalityRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Demographic\ReligionRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Demographic\ResidentRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Economic\EconomicRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Education\EducationRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Health\HealthRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Health\InfantHealthRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Health\WomenHealthRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Migration\MigrationRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Skill\SkillRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Sociocivic\SociocivicRepositoryInterface;
use App\Repositories\Interfaces\Setting\SettingRepositoryInterface;
use App\Repositories\Interfaces\UserManagement\RoleInterface;
use App\Repositories\Interfaces\UserManagement\UserRepositoryInterface;
use App\Repositories\Interfaces\UserManagement\UserRoleRepositoryInterface;
use App\Repositories\Interfaces\UserManagement\UserStatusInterface;
use App\Repositories\Logs\AuditLogRepository;
use App\Repositories\Logs\UserLogRepository;
use App\Repositories\Lookups\LookupRepository;
use App\Repositories\ResidentManagement\Ctc\CtcRepository;
use App\Repositories\ResidentManagement\Demographic\EthnicityRepository;
use App\Repositories\ResidentManagement\Demographic\NationalityRepository;
use App\Repositories\ResidentManagement\Demographic\ReligionRepository;
use App\Repositories\ResidentManagement\Demographic\ResidentRepository;
use App\Repositories\ResidentManagement\Economic\EconomicRepository;
use App\Repositories\ResidentManagement\Education\EducationRepository;
use App\Repositories\ResidentManagement\Health\HealthRepository;
use App\Repositories\ResidentManagement\Health\InfantHealthRepository;
use App\Repositories\ResidentManagement\Health\WomenHealthRepository;
use App\Repositories\ResidentManagement\Migration\MigrationRepository;
use App\Repositories\ResidentManagement\Skill\SkillRepository;
use App\Repositories\ResidentManagement\Sociocivic\SociocivicRepository;
use App\Repositories\Setting\SettingRepository;
use App\Repositories\UserManagement\RoleRepository;
use App\Repositories\UserManagement\UserRepository;
use App\Repositories\UserManagement\UserRoleRepository;
use App\Repositories\UserManagement\UserStatusRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds each repository interface to its concrete implementation.
 * This provider is registered in bootstrap/app.php.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserLogRepositoryInterface::class, UserLogRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(AuditLogRepositoryInterface::class, AuditLogRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserRoleRepositoryInterface::class, UserRoleRepository::class);
        $this->app->bind(PersonnelPositionRepositoryInterface::class, PersonnelPositionRepository::class);
        $this->app->bind(BarangayPersonnelRepositoryInterface::class, BarangayPersonnelRepository::class);
        $this->app->bind(RoleInterface::class, RoleRepository::class);
        $this->app->bind(UserStatusInterface::class, UserStatusRepository::class);
        $this->app->bind(HouseholdRepositoryInterface::class, HouseholdRepository::class);
        $this->app->bind(HouseholdAssessmentRepositoryInterface::class, HouseholdAssessmentRepository::class);
        $this->app->bind(ResidentRepositoryInterface::class, ResidentRepository::class);
        $this->app->bind(StreetRepositoryInterface::class, StreetRepository::class);
        $this->app->bind(NationalityRepositoryInterface::class, NationalityRepository::class);
        $this->app->bind(EthnicityRepositoryInterface::class, EthnicityRepository::class);
        $this->app->bind(ReligionRepositoryInterface::class, ReligionRepository::class);
        $this->app->bind(LookupRepositoryInterface::class, LookupRepository::class);
        $this->app->bind(EducationRepositoryInterface::class, EducationRepository::class);
        $this->app->bind(EconomicRepositoryInterface::class, EconomicRepository::class);
        $this->app->bind(InfantHealthRepositoryInterface::class, InfantHealthRepository::class);
        $this->app->bind(HealthRepositoryInterface::class, HealthRepository::class);
        $this->app->bind(WomenHealthRepositoryInterface::class, WomenHealthRepository::class);
        $this->app->bind(SociocivicRepositoryInterface::class, SociocivicRepository::class);
        $this->app->bind(MigrationRepositoryInterface::class, MigrationRepository::class);
        $this->app->bind(CtcRepositoryInterface::class, CtcRepository::class);
        $this->app->bind(SkillRepositoryInterface::class, SkillRepository::class);
    }
}
