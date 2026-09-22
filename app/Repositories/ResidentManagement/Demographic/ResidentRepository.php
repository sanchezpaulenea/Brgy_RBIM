<?php

namespace App\Repositories\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Resident;
use App\Repositories\Interfaces\ResidentManagement\Demographic\ResidentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ResidentRepository implements ResidentRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return [
            'household',
            'clan',
            'sex',
            'nationality',
            'religion',
            'ethnicity',
            'maritalStatus',
            'migration.residentType',
            'status',
            'relationshipToHouseholdHead',
        ];
    }

    /**
     * @param  array{
     *     search?: string,
     *     household_id?: int,
     *     sex_id?: int,
     *     resident_type_id?: int,
     *     resident_status_id?: int,
     *     age_min?: int,
     *     age_max?: int
     * }  $filters
     * @return Collection<int, Resident>
     */
    public function list(array $filters = []): Collection
    {
        return Resident::query()
            ->with($this->defaultRelations())
            ->withExists($this->sectionExistsRelations())
            ->when(
                ! empty($filters['household_id']),
                fn ($query) => $query->where('household_id', $filters['household_id']),
            )
            ->when(
                ! empty($filters['sex_id']),
                fn ($query) => $query->where('sex_id', $filters['sex_id']),
            )
            ->when(
                ! empty($filters['resident_type_id']),
                fn ($query) => $query->whereHas(
                    'migration',
                    fn ($migrationQuery) => $migrationQuery->where('resident_type_id', $filters['resident_type_id']),
                ),
            )
            ->when(
                ! empty($filters['resident_status_id']),
                fn ($query) => $query->where('resident_status_id', $filters['resident_status_id']),
            )
            ->when(
                array_key_exists('age_min', $filters) && $filters['age_min'] !== null && $filters['age_min'] !== '',
                fn ($query) => $query->whereRaw(
                    'TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= ?',
                    [(int) $filters['age_min']],
                ),
            )
            ->when(
                array_key_exists('age_max', $filters) && $filters['age_max'] !== null && $filters['age_max'] !== '',
                fn ($query) => $query->whereRaw(
                    'TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= ?',
                    [(int) $filters['age_max']],
                ),
            )
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $term = '%'.$this->escapeLike(mb_strtolower(trim((string) $filters['search']))).'%';

                $query->where(function ($searchQuery) use ($term) {
                    $searchQuery
                        ->whereRaw('LOWER(last_name) LIKE ? ESCAPE \'\\\\\'', [$term])
                        ->orWhereRaw('LOWER(first_name) LIKE ? ESCAPE \'\\\\\'', [$term])
                        ->orWhereRaw('LOWER(COALESCE(middle_name, \'\')) LIKE ? ESCAPE \'\\\\\'', [$term])
                        ->orWhereRaw('LOWER(COALESCE(suffix, \'\')) LIKE ? ESCAPE \'\\\\\'', [$term])
                        ->orWhereRaw(
                            'LOWER(CONCAT(last_name, \', \', first_name, \' \', COALESCE(middle_name, \'\'))) LIKE ? ESCAPE \'\\\\\'',
                            [$term],
                        )
                        ->orWhereHas('household', function ($householdQuery) use ($term) {
                            $householdQuery
                                ->whereRaw('CAST(household_id AS CHAR) LIKE ? ESCAPE \'\\\\\'', [$term])
                                ->orWhereRaw('LOWER(COALESCE(house_lot, \'\')) LIKE ? ESCAPE \'\\\\\'', [$term])
                                ->orWhereHas(
                                    'street',
                                    fn ($streetQuery) => $streetQuery->whereRaw(
                                        'LOWER(street_name) LIKE ? ESCAPE \'\\\\\'',
                                        [$term],
                                    ),
                                )
                                ->orWhereHas(
                                    'head',
                                    fn ($headQuery) => $headQuery
                                        ->whereRaw('LOWER(last_name) LIKE ? ESCAPE \'\\\\\'', [$term])
                                        ->orWhereRaw('LOWER(first_name) LIKE ? ESCAPE \'\\\\\'', [$term]),
                                );
                        });
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * @return Collection<int, Resident>
     */
    public function all(): Collection
    {
        return $this->list();
    }

    public function findById(int $residentId): ?Resident
    {
        return Resident::query()
            ->with(array_merge($this->defaultRelations(), $this->profileRelations()))
            ->where('resident_id', $residentId)
            ->first();
    }

    public function countByHousehold(int $householdId): int
    {
        return Resident::query()
            ->where('household_id', $householdId)
            ->count();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Resident
    {
        return $this->writeAllowingUnspecifiedLookups($attributes, function () use ($attributes) {
            $resident = Resident::create($attributes);

            return $resident->load($this->defaultRelations());
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Resident $resident, array $attributes): Resident
    {
        return $this->writeAllowingUnspecifiedLookups($attributes, function () use ($resident, $attributes) {
            $resident->fill($attributes);
            $resident->save();

            return $resident->fresh($this->defaultRelations()) ?? $resident;
        });
    }

    /**
     * Nationality, religion, and ethnicity may be stored as 0 when the encoder
     * leaves them blank. Those lookup tables have no id 0, so MySQL rejects the
     * write unless foreign-key checks are off for this statement only.
     *
     * @param  array<string, mixed>  $attributes
     * @param  callable(): Resident  $write
     */
    private function writeAllowingUnspecifiedLookups(array $attributes, callable $write): Resident
    {
        if (! $this->hasUnspecifiedOptionalLookup($attributes)) {
            return $write();
        }

        $this->disableForeignKeyChecks();

        try {
            return $write();
        } finally {
            $this->enableForeignKeyChecks();
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function hasUnspecifiedOptionalLookup(array $attributes): bool
    {
        foreach (Resident::OPTIONAL_LOOKUP_FIELDS as $field) {
            if (array_key_exists($field, $attributes) && (int) $attributes[$field] === Resident::LOOKUP_UNSPECIFIED) {
                return true;
            }
        }

        return false;
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

    /**
     * @return list<string>
     */
    private function sectionExistsRelations(): array
    {
        return [
            'education',
            'economic',
            'infantHealth',
            'health',
            'womenHealth',
            'sociocivic',
            'migration',
            'communityTaxCert',
            'skillsDevelopment',
        ];
    }

    /**
     * @return list<string>
     */
    private function profileRelations(): array
    {
        return [
            'education.highestLvlOfEduc',
            'education.currentEnrollmentStatus',
            'education.schoolLvl',
            'economic.sourceOfIncome',
            'economic.statusOfWorkBusiness',
            'infantHealth.placeOfDelivery',
            'infantHealth.birthAttendant',
            'infantHealth.immunization',
            'health.healthInsurance',
            'health.facilityVisitedPast12Mos',
            'health.facilityVisitReason',
            'health.womenHealth.familyPlanningMethod',
            'health.womenHealth.sourceOfFpMethod',
            'sociocivic.soloParentStatus',
            'migration.residentType',
            'migration.reasonForLeaving',
            'migration.reasonForTransfer',
            'communityTaxCert',
            'skillsDevelopment.skillType',
        ];
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
