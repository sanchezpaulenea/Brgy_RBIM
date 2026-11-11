<?php

namespace App\Repositories\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Demographic\Suffix;
use App\Repositories\Interfaces\ResidentManagement\Demographic\ResidentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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
            'nameSuffix',
            'birthProvince',
            'birthMunicipality',
            'birthCountry',
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
        return $this->matchingResidents($filters)
            ->with($this->defaultRelations())
            ->withExists($this->sectionExistsRelations())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    public function paginate(array $filters, int $perPage, int $page): LengthAwarePaginator
    {
        return $this->paginateMatching($filters, $perPage, $page, false);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Resident>
     */
    public function listNames(array $filters = []): Collection
    {
        return $this->matchingResidents($filters)
            ->select(['resident_id', 'last_name', 'first_name', 'middle_name', 'suffix_id'])
            ->with('nameSuffix')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('resident_id')
            ->get();
    }

    public function paginateNames(array $filters, int $perPage, int $page): LengthAwarePaginator
    {
        return $this->paginateMatching($filters, $perPage, $page, true);
    }

    private function paginateMatching(array $filters, int $perPage, int $page, bool $namesOnly): LengthAwarePaginator
    {
        $total = $this->matchingResidents($filters)->count();

        $query = $this->matchingResidents($filters)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('resident_id');

        if ($namesOnly) {
            $query->select(['resident_id', 'last_name', 'first_name', 'middle_name', 'suffix_id'])
                ->with('nameSuffix');
        } else {
            $query->with($this->defaultRelations())
                ->withExists($this->sectionExistsRelations());
        }

        return new LengthAwarePaginator(
            $query->forPage($page, $perPage)->get(),
            $total,
            $perPage,
            $page,
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Resident>
     */
    private function matchingResidents(array $filters): Builder
    {
        return Resident::query()
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
                        ->orWhereHas('nameSuffix', function ($suffixQuery) use ($term) {
                            $suffixQuery
                                ->whereRaw('LOWER(suffix) LIKE ? ESCAPE \'\\\\\'', [$term])
                                ->whereRaw('LOWER(suffix) NOT LIKE ?', ['not applic%']);
                        })
                        ->orWhereRaw(
                            'LOWER(CONCAT(last_name, \', \', first_name, \' \', COALESCE(middle_name, \'\'))) LIKE ? ESCAPE \'\\\\\'',
                            [$term],
                        )
                        ->orWhereHas('household', function ($householdQuery) use ($term) {
                            $householdQuery
                                ->whereRaw('CAST(household_id AS CHAR) LIKE ? ESCAPE \'\\\\\'', [$term])
                                ->orWhereRaw('CAST(house_number AS CHAR) LIKE ? ESCAPE \'\\\\\'', [$term])
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
            });
    }

    /**
     * @return Collection<int, Resident>
     */
    public function all(): Collection
    {
        return $this->list();
    }

    public function findMatchingIdentity(
        string $lastName,
        string $firstName,
        ?string $middleName,
        ?string $suffix,
        string $dateOfBirth,
        ?int $householdId = null,
    ): ?Resident {
        $query = Resident::query()
            ->whereRaw('LOWER(last_name) = ?', [mb_strtolower(trim($lastName))])
            ->whereRaw('LOWER(first_name) = ?', [mb_strtolower(trim($firstName))])
            ->whereDate('date_of_birth', substr($dateOfBirth, 0, 10));

        $this->whereBlankOrEquals($query, 'middle_name', $middleName);

        $suffixId = Suffix::idForIdentityMatch($suffix);

        if ($suffixId === null) {
            $query->whereRaw('0 = 1');
        } else {
            $query->where('suffix_id', $suffixId);
        }

        if ($householdId !== null) {
            $query->where('household_id', $householdId);
        }

        return $query->first();
    }

    public function findById(int $residentId): ?Resident
    {
        return Resident::query()
            ->with(array_merge($this->defaultRelations(), $this->profileRelations()))
            ->where('resident_id', $residentId)
            ->first();
    }

    public function findForCertificate(int $residentId): ?Resident
    {
        return Resident::query()
            ->with(['status', 'sex', 'household.street', 'nationality', 'maritalStatus', 'ethnicity'])
            ->where('resident_id', $residentId)
            ->first();
    }

    public function searchByName(string $term, int $limit = 20): Collection
    {
        $like = '%'.$this->escapeLike(mb_strtolower(trim($term))).'%';

        return Resident::query()
            ->with('status')
            ->where(function ($query) use ($like) {
                $query->whereRaw('LOWER(first_name) LIKE ? ESCAPE \'\\\\\'', [$like])
                    ->orWhereRaw('LOWER(COALESCE(middle_name, \'\')) LIKE ? ESCAPE \'\\\\\'', [$like])
                    ->orWhereRaw('LOWER(last_name) LIKE ? ESCAPE \'\\\\\'', [$like]);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit($limit)
            ->get();
    }

    public function countByHousehold(int $householdId): int
    {
        return Resident::query()
            ->where('household_id', $householdId)
            ->count();
    }

    public function childrenInHousehold(int $householdId, int $exceptResidentId): Collection
    {
        return Resident::query()
            ->with('relationshipToHouseholdHead')
            ->where('household_id', $householdId)
            ->where('resident_id', '!=', $exceptResidentId)
            ->whereHas('relationshipToHouseholdHead', function ($query): void {
                $query->whereIn('relationship_to_hh', [
                    'Son',
                    'Daughter',
                    'Stepson',
                    'Stepdaughter',
                    'Grandson',
                    'Granddaughter',
                ]);
            })
            ->orderByRaw('date_of_birth IS NULL')
            ->orderBy('date_of_birth')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Resident
    {
        $resident = Resident::create($this->persistableAttributes($attributes, true));

        return $resident->load($this->defaultRelations());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Resident $resident, array $attributes): Resident
    {
        $resident->fill($this->persistableAttributes($attributes, false));
        $resident->save();

        return $resident->fresh($this->defaultRelations()) ?? $resident;
    }

    /**
     * The resident table stores suffix_id. A blank suffix is the Not Applicable row.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function persistableAttributes(array $attributes, bool $requireSuffix): array
    {
        $hasBirthPlace = array_key_exists('birth_barangay_id', $attributes)
            || array_key_exists('birth_city_municipality_id', $attributes)
            || array_key_exists('birth_province_id', $attributes)
            || array_key_exists('birth_country_id', $attributes)
            || array_key_exists('birth_city_municipality', $attributes)
            || array_key_exists('birth_province', $attributes)
            || array_key_exists('birth_country', $attributes);

        unset(
            $attributes['birth_city_municipality'],
            $attributes['birth_province'],
            $attributes['birth_country'],
        );

        if ($hasBirthPlace) {
            $birthPlace = Resident::birthPlaceIds($attributes);

            $attributes['birth_city_municipality_id'] = $birthPlace['birth_city_municipality_id'];
            $attributes['birth_province_id'] = $birthPlace['birth_province_id'];
            $attributes['birth_country_id'] = $birthPlace['birth_country_id'];

            if (Resident::birthBarangayColumnExists()) {
                $attributes['birth_barangay_id'] = $birthPlace['birth_barangay_id'];
            } else {
                unset($attributes['birth_barangay_id']);
            }
        }

        $hasSuffixId = array_key_exists('suffix_id', $attributes)
            && $attributes['suffix_id'] !== null
            && $attributes['suffix_id'] !== '';

        if ($hasSuffixId) {
            $attributes['suffix_id'] = (int) $attributes['suffix_id'];
            unset($attributes['suffix']);

            return $attributes;
        }

        if ($requireSuffix || array_key_exists('suffix', $attributes)) {
            $label = isset($attributes['suffix']) && is_string($attributes['suffix'])
                ? $attributes['suffix']
                : null;
            $attributes['suffix_id'] = Suffix::idMatchingLabel($label);
        }

        unset($attributes['suffix']);

        return $attributes;
    }

    /**
     * @param  Builder<Resident>  $query
     */
    private function whereBlankOrEquals(Builder $query, string $column, ?string $value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            $query->where(function (Builder $builder) use ($column): void {
                $builder->whereNull($column)->orWhere($column, '');
            });

            return;
        }

        $query->whereRaw('LOWER('.$column.') = ?', [mb_strtolower($value)]);
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
            'education.schoolBarangay.municipalityCity.province',
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
            'skillsDevelopment.training',
        ];
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
