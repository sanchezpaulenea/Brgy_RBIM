<?php

namespace App\Repositories\Interfaces;

use App\Models\Certificate\Certificate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CertificateRepositoryInterface
{
    /**
     * @param  array{
     *     resident_id: int,
     *     date_of_issue: string,
     *     purpose_of_request: string,
     *     type_of_certificate_id: int,
     *     issuer_id: int,
     *     representative_name: ?string,
     *     representative_relationship: ?string,
     *     date_of_death: ?string,
     *     include_income_amount: int,
     *     stated_income_amount: ?string
     * }  $attributes
     */
    public function create(array $attributes): Certificate;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Certificate $certificate, array $attributes): Certificate;

    public function paginateForResident(int $residentId, int $perPage = 10, int $page = 1): LengthAwarePaginator;

    /**
     * @return Collection<int, object>
     */
    public function countsByTypeForResident(int $residentId): Collection;
}
