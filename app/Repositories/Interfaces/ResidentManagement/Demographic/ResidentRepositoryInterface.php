<?php

namespace App\Repositories\Interfaces\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ResidentRepositoryInterface
{
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
    public function list(array $filters = []): Collection;

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Resident>
     */
    public function paginate(array $filters, int $perPage, int $page): LengthAwarePaginator;

    /**
     * Name fields used by duplicate detection. No profiling relations.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Resident>
     */
    public function listNames(array $filters = []): Collection;

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Resident>
     */
    public function paginateNames(array $filters, int $perPage, int $page): LengthAwarePaginator;

    public function findById(int $residentId): ?Resident;

    public function findMatchingIdentity(
        string $lastName,
        string $firstName,
        ?string $middleName,
        ?string $suffix,
        string $dateOfBirth,
        ?int $householdId = null,
    ): ?Resident;

    public function findForCertificate(int $residentId): ?Resident;

    /**
     * @return Collection<int, Resident>
     */
    public function searchByName(string $term, int $limit = 20): Collection;

    public function countByHousehold(int $householdId): int;

    /**
     * Household members recorded as children, excluding the certificate owner.
     *
     * @return Collection<int, Resident>
     */
    public function childrenInHousehold(int $householdId, int $exceptResidentId): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Resident;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Resident $resident, array $attributes): Resident;
}
