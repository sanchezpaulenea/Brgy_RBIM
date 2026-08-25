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
        ], true);
    }

    public function orderColumn(): string
    {
        return match ($this) {
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

    /**
     * @return array<string, mixed>
     */
    public function format(Model $model): array
    {
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
