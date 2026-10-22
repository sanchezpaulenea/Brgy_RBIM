<?php

namespace App\Repositories;

use App\Models\Certificate\Certificate;
use App\Models\Certificate\CertificateSoloParent;
use App\Models\Certificate\CertificateSoloParentChild;
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
     *     issuer_id: int,
     *     representative_name: ?string,
     *     representative_relationship: ?string,
     *     date_of_death: ?string,
     *     include_income_amount: int,
     *     stated_income_amount: ?string
     * }  $attributes
     */
    public function create(array $attributes): Certificate
    {
        $soloParent = $attributes['solo_parent'] ?? null;
        unset($attributes['solo_parent']);

        $certificate = Certificate::query()->create($attributes);
        $this->syncSoloParent($certificate, is_array($soloParent) ? $soloParent : null);

        return $certificate->refresh();
    }

    public function update(Certificate $certificate, array $attributes): Certificate
    {
        $soloParent = $attributes['solo_parent'] ?? null;
        unset($attributes['solo_parent']);

        $certificate->fill($attributes);
        $certificate->save();
        $this->syncSoloParent($certificate, is_array($soloParent) ? $soloParent : null);

        return $certificate->refresh();
    }

    public function paginateForResident(int $residentId, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return Certificate::query()
            ->with(['type', 'issuer.personnel', 'soloParent', 'soloParentChildren'])
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

    /**
     * @param  array{
     *     circumstance: string,
     *     president_name: string,
     *     president_address: string,
     *     neighbor_name: string,
     *     neighbor_address: string,
     *     kagawad_name: string,
     *     children: list<array{child_name: string, date_of_birth: string}>
     * }|null  $soloParent
     */
    private function syncSoloParent(Certificate $certificate, ?array $soloParent): void
    {
        $certificateId = (int) $certificate->certificate_id;

        CertificateSoloParentChild::query()->where('certificate_id', $certificateId)->delete();

        if ($soloParent === null) {
            CertificateSoloParent::query()->where('certificate_id', $certificateId)->delete();

            return;
        }

        CertificateSoloParent::query()->updateOrCreate(
            ['certificate_id' => $certificateId],
            [
                'circumstance' => $soloParent['circumstance'],
                'president_name' => $soloParent['president_name'],
                'president_address' => $soloParent['president_address'],
                'neighbor_name' => $soloParent['neighbor_name'],
                'neighbor_address' => $soloParent['neighbor_address'],
                'kagawad_name' => $soloParent['kagawad_name'],
            ],
        );

        foreach ($soloParent['children'] as $child) {
            CertificateSoloParentChild::query()->create([
                'certificate_id' => $certificateId,
                'child_name' => $child['child_name'],
                'date_of_birth' => $child['date_of_birth'],
            ]);
        }
    }
}
