<?php

namespace App\Enums;

use App\Models\Audit\Action;
use App\Models\Authentication\LoginStatus;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\BarangayPersonnel\PersonnelStatus;
use App\Models\HouseholdManagement\CensusStatus;
use App\Models\HouseholdManagement\Clan;
use App\Models\HouseholdManagement\HouseholdStatus;
use App\Models\ResidentManagement\Demographic\MaritalStatus;
use App\Models\ResidentManagement\Demographic\RelationshipToHouseholdHead;
use App\Models\ResidentManagement\Demographic\ResidentStatus;
use App\Models\ResidentManagement\Demographic\ResidentType;
use App\Models\ResidentManagement\Demographic\Sex;
use App\Models\ResidentManagement\Economic\SourceOfIncome;
use App\Models\ResidentManagement\Economic\StatusOfWorkBusiness;
use App\Models\ResidentManagement\Education\CurrentEnrollmentStatus;
use App\Models\ResidentManagement\Education\HighestLvlOfEduc;
use App\Models\ResidentManagement\Education\SchoolLvl;
use App\Models\ResidentManagement\Health\Disability;
use App\Models\ResidentManagement\Health\BirthAttendant;
use App\Models\ResidentManagement\Health\FacilityVisitedPast12Mos;
use App\Models\ResidentManagement\Health\FacilityVisitReason;
use App\Models\ResidentManagement\Health\FamilyPlanningMethod;
use App\Models\ResidentManagement\Health\HealthInsurance;
use App\Models\ResidentManagement\Health\PlaceOfDelivery;
use App\Models\ResidentManagement\Health\SourceOfFPMethod;
use App\Models\ResidentManagement\Migration\ReasonForLeaving;
use App\Models\ResidentManagement\Migration\ReasonForTransfer;
use App\Models\ResidentManagement\Skill\SkillType;
use App\Models\ResidentManagement\Sociocivic\SoloParentStatus;
use App\Models\UserManagement\Permission;
use App\Models\UserManagement\UserStatus;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

enum LookupType: string
{
    case PersonnelPosition = 'personnel-position';
    case PersonnelStatus = 'personnel-status';
    case UserStatus = 'user-status';
    case LoginStatus = 'login-status';
    case Action = 'action';
    case Permission = 'permission';
    case CensusStatus = 'census-status';
    case Clan = 'clan';
    case HouseholdStatus = 'household-status';
    case MaritalStatus = 'marital-status';
    case RelationshipToHouseholdHead = 'relationship-to-hh';
    case ResidentStatus = 'resident-status';
    case ResidentType = 'resident-type';
    case Sex = 'sex';
    case HighestLvlOfEduc = 'highest-lvl-of-educ';
    case CurrentEnrollmentStatus = 'current-enrollment-status';
    case SchoolLvl = 'school-lvl';
    case SourceOfIncome = 'source-of-income';
    case StatusOfWorkBusiness = 'status-of-work-business';
    case PlaceOfDelivery = 'place-of-delivery';
    case BirthAttendant = 'birth-attendant';
    case HealthInsurance = 'health-insurance';
    case FacilityVisitedPast12Mos = 'facility-visited-past-12mos';
    case FacilityVisitReason = 'facility-visit-reason';
    case Disability = 'disability';
    case FamilyPlanningMethod = 'family-planning-method';
    case SourceOfFpMethod = 'source-of-fp-method';
    case SoloParentStatus = 'solo-parent-status';
    case ReasonForLeaving = 'reason-for-leaving';
    case ReasonForTransfer = 'reason-for-transfer';
    case SkillType = 'skill-type';

    /**
     * @return class-string<Model>
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::PersonnelPosition => PersonnelPosition::class,
            self::PersonnelStatus => PersonnelStatus::class,
            self::UserStatus => UserStatus::class,
            self::LoginStatus => LoginStatus::class,
            self::Action => Action::class,
            self::Permission => Permission::class,
            self::CensusStatus => CensusStatus::class,
            self::Clan => Clan::class,
            self::HouseholdStatus => HouseholdStatus::class,
            self::MaritalStatus => MaritalStatus::class,
            self::RelationshipToHouseholdHead => RelationshipToHouseholdHead::class,
            self::ResidentStatus => ResidentStatus::class,
            self::ResidentType => ResidentType::class,
            self::Sex => Sex::class,
            self::HighestLvlOfEduc => HighestLvlOfEduc::class,
            self::CurrentEnrollmentStatus => CurrentEnrollmentStatus::class,
            self::SchoolLvl => SchoolLvl::class,
            self::SourceOfIncome => SourceOfIncome::class,
            self::StatusOfWorkBusiness => StatusOfWorkBusiness::class,
            self::PlaceOfDelivery => PlaceOfDelivery::class,
            self::BirthAttendant => BirthAttendant::class,
            self::HealthInsurance => HealthInsurance::class,
            self::FacilityVisitedPast12Mos => FacilityVisitedPast12Mos::class,
            self::FacilityVisitReason => FacilityVisitReason::class,
            self::Disability => Disability::class,
            self::FamilyPlanningMethod => FamilyPlanningMethod::class,
            self::SourceOfFpMethod => SourceOfFPMethod::class,
            self::SoloParentStatus => SoloParentStatus::class,
            self::ReasonForLeaving => ReasonForLeaving::class,
            self::ReasonForTransfer => ReasonForTransfer::class,
            self::SkillType => SkillType::class,
        };
    }

    public function isWritable(): bool
    {
        return $this === self::PersonnelPosition;
    }

    /**
     * Seeded Increment 2 tables with no create/delete API.
     */
    public function isReadOnlyReference(): bool
    {
        return in_array($this, [
            self::CensusStatus,
            self::Clan,
            self::HouseholdStatus,
            self::MaritalStatus,
            self::RelationshipToHouseholdHead,
            self::ResidentStatus,
            self::ResidentType,
            self::Sex,
            ...self::incrementTwoLookups(),
        ], true);
    }

    /**
     * @return list<self>
     */
    public static function incrementTwoLookups(): array
    {
        return [
            self::HighestLvlOfEduc,
            self::CurrentEnrollmentStatus,
            self::SchoolLvl,
            self::SourceOfIncome,
            self::StatusOfWorkBusiness,
            self::PlaceOfDelivery,
            self::BirthAttendant,
            self::HealthInsurance,
            self::FacilityVisitedPast12Mos,
            self::FacilityVisitReason,
            self::Disability,
            self::FamilyPlanningMethod,
            self::SourceOfFpMethod,
            self::SoloParentStatus,
            self::ReasonForLeaving,
            self::ReasonForTransfer,
            self::SkillType,
        ];
    }

    public function orderColumn(): string
    {
        return $this->labelColumn() ?? match ($this) {
            self::CensusStatus => 'status_name',
            self::Clan => 'clan_name',
            self::HouseholdStatus => 'household_status',
            self::MaritalStatus => 'marital_status',
            self::RelationshipToHouseholdHead => 'relationship_to_hh',
            self::ResidentStatus => 'resident_status',
            self::ResidentType => 'resident_type',
            self::Sex => 'sex',
            default => 'id',
        };
    }

    public function idColumn(): ?string
    {
        return match ($this) {
            self::HighestLvlOfEduc => 'highest_lvl_of_educ_id',
            self::CurrentEnrollmentStatus => 'current_enrollment_status_id',
            self::SchoolLvl => 'school_lvl_id',
            self::SourceOfIncome => 'source_of_income_id',
            self::StatusOfWorkBusiness => 'status_of_work_business_id',
            self::PlaceOfDelivery => 'place_of_delivery_id',
            self::BirthAttendant => 'birth_attendant_id',
            self::HealthInsurance => 'health_insurance_id',
            self::FacilityVisitedPast12Mos => 'facility_visited_past_12mos_id',
            self::FacilityVisitReason => 'facility_visit_reason_id',
            self::Disability => 'disability_id',
            self::FamilyPlanningMethod => 'family_planning_method_id',
            self::SourceOfFpMethod => 'source_of_fp_method_id',
            self::SoloParentStatus => 'solo_parent_status_id',
            self::ReasonForLeaving => 'reason_for_leaving_id',
            self::ReasonForTransfer => 'reason_for_transfer_id',
            self::SkillType => 'skill_type_id',
            default => null,
        };
    }

    public function labelColumn(): ?string
    {
        return match ($this) {
            self::HighestLvlOfEduc => 'lvl_of_educ',
            self::CurrentEnrollmentStatus => 'current_enrollement_status',
            self::SchoolLvl => 'school_lvl',
            self::SourceOfIncome => 'source_of_income',
            self::StatusOfWorkBusiness => 'status_of_work_business',
            self::PlaceOfDelivery => 'place_of_delivery',
            self::BirthAttendant => 'birth_attendant',
            self::HealthInsurance => 'health_insurance',
            self::FacilityVisitedPast12Mos => 'facility_visited_past_12mos',
            self::FacilityVisitReason => 'facility_visit_reason',
            self::Disability => 'disability',
            self::FamilyPlanningMethod => 'family_planning_method',
            self::SourceOfFpMethod => 'source_of_fp_method',
            self::SoloParentStatus => 'solo_parent_status',
            self::ReasonForLeaving => 'reason_for_leaving',
            self::ReasonForTransfer => 'reason_for_transfer',
            self::SkillType => 'skill_type',
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function format(Model $model): array
    {
        $idColumn = $this->idColumn();
        $labelColumn = $this->labelColumn();

        if ($idColumn !== null && $labelColumn !== null) {
            return [
                'id' => $model->getKey(),
                'label' => (string) $model->getAttribute($labelColumn),
                $idColumn => $model->getKey(),
                $labelColumn => $model->getAttribute($labelColumn),
            ];
        }

        return match ($this) {
            self::CensusStatus => [
                'id' => $model->getKey(),
                'label' => (string) $model->getAttribute('status_name'),
                'census_status_id' => $model->getKey(),
                'status_code' => $model->getAttribute('status_code'),
                'status_name' => $model->getAttribute('status_name'),
            ],
            self::Clan => [
                'id' => $model->getKey(),
                'label' => (string) $model->getAttribute('clan_name'),
                'clan_id' => $model->getKey(),
                'clan_name' => $model->getAttribute('clan_name'),
            ],
            self::HouseholdStatus => [
                'id' => $model->getKey(),
                'label' => (string) $model->getAttribute('household_status'),
                'household_status_id' => $model->getKey(),
                'household_status' => $model->getAttribute('household_status'),
            ],
            self::MaritalStatus => [
                'id' => $model->getKey(),
                'label' => (string) $model->getAttribute('marital_status'),
                'marital_status_id' => $model->getKey(),
                'marital_status' => $model->getAttribute('marital_status'),
            ],
            self::RelationshipToHouseholdHead => [
                'id' => $model->getKey(),
                'label' => (string) $model->getAttribute('relationship_to_hh'),
                'relationship_to_hh_id' => $model->getKey(),
                'relationship_to_hh' => $model->getAttribute('relationship_to_hh'),
            ],
            self::ResidentStatus => [
                'id' => $model->getKey(),
                'label' => (string) $model->getAttribute('resident_status'),
                'resident_status_id' => $model->getKey(),
                'resident_status' => $model->getAttribute('resident_status'),
            ],
            self::ResidentType => [
                'id' => $model->getKey(),
                'label' => (string) $model->getAttribute('resident_type'),
                'resident_type_id' => $model->getKey(),
                'resident_type' => $model->getAttribute('resident_type'),
            ],
            self::Sex => [
                'id' => $model->getKey(),
                'label' => (string) $model->getAttribute('sex'),
                'sex_id' => $model->getKey(),
                'sex' => $model->getAttribute('sex'),
            ],
            default => [
                'id' => $model->getKey(),
                'label' => (string) $model->getKey(),
            ],
        };
    }

    public static function fromSlug(string $slug): self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $slug) {
                return $case;
            }
        }

        throw new NotFoundHttpException("Lookup type [{$slug}] is not supported.");
    }
}
