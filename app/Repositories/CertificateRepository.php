<?php

namespace App\Repositories;

use App\Models\Certificate\Certificate;
use App\Models\Certificate\TypeOfCertificate;
use App\Repositories\Interfaces\CertificateRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CertificateRepository implements CertificateRepositoryInterface
{
    /**
     * @param  array{
     *     resident_id: int,
     *     date_of_issue: string,
     *     purpose_of_request: string,
     *     type_of_certificate_id: int,
     *     issuer_id: int
     * }  $attributes
     */
    public function create(array $attributes): Certificate
    {
        return Certificate::query()->create($attributes);
    }

    public function paginateForResident(int $residentId, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return Certificate::query()
            ->with(['type', 'issuer.personnel'])
            ->where('resident_id', $residentId)
            ->orderByDesc('date_of_issue')
            ->orderByDesc('certificate_id')
            ->paginate($perPage, ['*'], 'page', max(1, $page));
    }

    public function countsByTypeForResident(int $residentId): Collection
    {
        $certificates = (new Certificate)->getTable();
        $types = (new TypeOfCertificate)->getTable();

        return Certificate::query()
            ->join(
                $types,
                "{$types}.type_of_certificate_id",
                '=',
                "{$certificates}.type_of_certificate_id",
            )
            ->where("{$certificates}.resident_id", $residentId)
            ->groupBy(
                "{$certificates}.type_of_certificate_id",
                "{$types}.type_of_certificate",
            )
            ->orderBy("{$types}.type_of_certificate")
            ->select([
                "{$certificates}.type_of_certificate_id",
                "{$types}.type_of_certificate",
            ])
            ->selectRaw('COUNT(*) as certificate_count')
            ->get();
    }
}
