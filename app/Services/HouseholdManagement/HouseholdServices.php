<?php

namespace App\Services\HouseholdManagement;

use App\Models\HouseholdManagement\Household;
use App\Models\HouseholdManagement\HouseholdStatus;
use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\RelationshipToHouseholdHead;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Demographic\ResidentStatus;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Demographic\ResidentRepositoryInterface;
use Illuminate\Support\Facades\DB;

class HouseholdServices
{
    public function __construct(
        protected HouseholdRepositoryInterface $householdRepository,
        protected ResidentRepositoryInterface $residentRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * Register a household and its head resident in one atomic transaction.
     *
     * household.head_resident_id and resident.household_id are circular NOT NULL
     * foreign keys. MySQL cannot defer those checks, so FK enforcement is turned
     * off only for the two inserts (and the head pointer update) inside this
     * transaction, then restored in finally so the connection never leaks the
     * disabled state.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createHousehold(User $performedBy, array $data): array
    {
        $headData = $data['head'];

        return DB::transaction(function () use ($performedBy, $data, $headData) {
            try {
                $this->disableForeignKeyChecks();

                $household = $this->householdRepository->create([
                    'clan_id' => $data['clan_id'],
                    'street_id' => $data['street_id'],
                    'house_lot' => $data['house_lot'] ?? null,
                    'block_num' => $data['block_num'] ?? null,
                    'building_name' => $data['building_name'] ?? null,
                    'unit_num' => $data['unit_num'] ?? null,
                    'registration_date' => now(),
                    'household_status_id' => $data['household_status_id'] ?? HouseholdStatus::ACTIVE,
                    'head_resident_id' => 0,
                ]);

                $resident = $this->residentRepository->create([
                    'last_name' => $headData['last_name'],
                    'first_name' => $headData['first_name'],
                    'middle_name' => $headData['middle_name'] ?? null,
                    'suffix' => $headData['suffix'] ?? null,
                    'relationship_to_hh_id' => RelationshipToHouseholdHead::HEAD,
                    'sex_id' => $headData['sex_id'],
                    'date_of_birth' => $headData['date_of_birth'],
                    'birth_city_municipality' => $headData['birth_city_municipality'],
                    'birth_province' => $headData['birth_province'],
                    'birth_country' => $headData['birth_country'],
                    'nationality_id' => $headData['nationality_id'],
                    'religion_id' => $headData['religion_id'],
                    'ethnicity_id' => $headData['ethnicity_id'],
                    'marital_status_id' => $headData['marital_status_id'],
                    'resident_type_id' => $headData['resident_type_id'],
                    'clan_id' => $headData['clan_id'] ?? $data['clan_id'],
                    'resident_status_id' => $headData['resident_status_id'] ?? ResidentStatus::ACTIVE,
                    'household_id' => $household->household_id,
                ]);

                $household = $this->householdRepository->updateHeadResident(
                    $household,
                    $resident->resident_id,
                );
            } finally {
                $this->enableForeignKeyChecks();
            }

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $household->household_id,
                description: 'Create household',
                oldValue: null,
                newValue: $this->householdAuditLabel($household),
                target: 'household',
                entity: 'household',
            );

            return $this->formatRecord($household);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Household $household): array
    {
        $household->loadMissing([
            'clan',
            'street',
            'status',
            'head.sex',
            'head.nationality',
            'head.religion',
            'head.ethnicity',
            'head.maritalStatus',
            'head.residentType',
            'head.status',
            'head.clan',
            'head.relationshipToHouseholdHead',
        ]);

        return [
            'household_id' => $household->household_id,
            'clan_id' => $household->clan_id,
            'clan_name' => $household->clan?->clan_name,
            'street_id' => $household->street_id,
            'street_name' => $household->street?->street_name,
            'house_lot' => $household->house_lot,
            'block_num' => $household->block_num,
            'building_name' => $household->building_name,
            'unit_num' => $household->unit_num,
            'registration_date' => $household->registration_date?->toDateTimeString(),
            'household_status_id' => $household->household_status_id,
            'household_status' => $household->status?->household_status,
            'head_resident_id' => $household->head_resident_id,
            'head' => $household->head !== null ? $this->formatHead($household->head) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatHead(Resident $resident): array
    {
        return [
            'resident_id' => $resident->resident_id,
            'last_name' => $resident->last_name,
            'first_name' => $resident->first_name,
            'middle_name' => $resident->middle_name,
            'suffix' => $resident->suffix,
            'full_name' => $this->fullName($resident),
            'relationship_to_hh_id' => $resident->relationship_to_hh_id,
            'relationship_to_hh' => $resident->relationshipToHouseholdHead?->relationship_to_hh,
            'sex_id' => $resident->sex_id,
            'sex' => $resident->sex?->sex,
            'date_of_birth' => $resident->date_of_birth?->format('Y-m-d'),
            'birth_city_municipality' => $resident->birth_city_municipality,
            'birth_province' => $resident->birth_province,
            'birth_country' => $resident->birth_country,
            'nationality_id' => $resident->nationality_id,
            'nationality' => $resident->nationality?->nationality,
            'religion_id' => $resident->religion_id,
            'religion' => $resident->religion?->religion,
            'ethnicity_id' => $resident->ethnicity_id,
            'ethnicity' => $resident->ethnicity?->ethnicity,
            'marital_status_id' => $resident->marital_status_id,
            'marital_status' => $resident->maritalStatus?->marital_status,
            'resident_type_id' => $resident->resident_type_id,
            'resident_type' => $resident->residentType?->resident_type,
            'clan_id' => $resident->clan_id,
            'clan_name' => $resident->clan?->clan_name,
            'resident_status_id' => $resident->resident_status_id,
            'resident_status' => $resident->status?->resident_status,
            'household_id' => $resident->household_id,
        ];
    }

    private function householdAuditLabel(Household $household): string
    {
        if ($household->head !== null) {
            return $this->fullName($household->head);
        }

        return 'Household '.$household->household_id;
    }

    private function fullName(Resident $resident): string
    {
        $givenNames = collect([
            $resident->first_name,
            $resident->middle_name,
            $resident->suffix,
        ])->filter()->implode(' ');

        if ($givenNames === '') {
            return $resident->last_name;
        }

        return $resident->last_name.', '.$givenNames;
    }

    private function disableForeignKeyChecks(): void
    {
        if (! $this->usesMysql()) {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    private function enableForeignKeyChecks(): void
    {
        if (! $this->usesMysql()) {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function usesMysql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
}
