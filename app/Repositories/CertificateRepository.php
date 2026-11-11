<?php

namespace App\Repositories;

use App\Models\Certificate\Certificate;
use App\Models\Certificate\CertificateSoloParent;
use App\Models\Certificate\CertificateSoloParentChild;
use App\Models\Certificate\DeathCertificate;
use App\Models\Certificate\LowIncomeCertificate;
use App\Models\Certificate\TypeOfCertificate;
use App\Models\ResidentManagement\Demographic\Resident;
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
        $death = $attributes['death'] ?? null;
        $lowIncome = $attributes['low_income'] ?? null;
        unset($attributes['solo_parent'], $attributes['death'], $attributes['low_income']);

        $certificate = Certificate::query()->create($attributes);
        $this->syncSoloParent($certificate, is_array($soloParent) ? $soloParent : null);
        $this->syncDeath($certificate, is_array($death) ? $death : null);
        $this->syncLowIncome($certificate, is_array($lowIncome) ? $lowIncome : null);

        return $certificate->refresh();
    }

    public function update(Certificate $certificate, array $attributes): Certificate
    {
        $soloParent = $attributes['solo_parent'] ?? null;
        $death = $attributes['death'] ?? null;
        $lowIncome = $attributes['low_income'] ?? null;
        unset($attributes['solo_parent'], $attributes['death'], $attributes['low_income']);

        $certificate->fill($attributes);
        $certificate->save();
        $this->syncSoloParent($certificate, is_array($soloParent) ? $soloParent : null);
        $this->syncDeath($certificate, is_array($death) ? $death : null);
        $this->syncLowIncome($certificate, is_array($lowIncome) ? $lowIncome : null);

        return $certificate->refresh();
    }

    public function paginateForResident(int $residentId, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return Certificate::query()
            ->with(['type', 'issuer.personnel', 'soloParent.president', 'soloParent.kagawad', 'soloParent.children', 'deathRecord.ethnicity', 'lowIncome', 'captain', 'maritalStatusResident.maritalStatus', 'genderResident.sex'])
            ->where('resident_id', $residentId)
            ->orderByDesc('date_of_issue')
            ->orderByDesc('certificate_id')
            ->paginate($perPage, ['*'], 'page', max(1, $page));
    }

    public function paginateResidentsWithCertificates(?string $search, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $residents = (new Resident)->getTable();

        $stats = Certificate::query()
            ->select('resident_id')
            ->selectRaw('MAX(date_of_issue) as latest_issue_at')
            ->selectRaw('COUNT(*) as certificate_count')
            ->groupBy('resident_id');

        $term = trim((string) $search);

        return Resident::query()
            ->with('status')
            ->joinSub($stats, 'certificate_stats', 'certificate_stats.resident_id', '=', "{$residents}.resident_id")
            ->select("{$residents}.*", 'certificate_stats.latest_issue_at', 'certificate_stats.certificate_count')
            ->when($term !== '', function ($query) use ($term, $residents) {
                $like = '%'.$this->escapeLike(mb_strtolower($term)).'%';

                $query->where(function ($inner) use ($like, $residents) {
                    $inner->whereRaw("LOWER({$residents}.first_name) LIKE ? ESCAPE '\\\\'", [$like])
                        ->orWhereRaw("LOWER(COALESCE({$residents}.middle_name, '')) LIKE ? ESCAPE '\\\\'", [$like])
                        ->orWhereRaw("LOWER({$residents}.last_name) LIKE ? ESCAPE '\\\\'", [$like]);
                });
            })
            ->orderByDesc('certificate_stats.latest_issue_at')
            ->orderByDesc("{$residents}.resident_id")
            ->paginate($perPage, ['*'], 'page', max(1, $page));
    }

    public function latestForResidents(array $residentIds): Collection
    {
        if ($residentIds === []) {
            return collect();
        }

        return Certificate::query()
            ->with('type')
            ->whereIn('resident_id', $residentIds)
            ->orderByDesc('date_of_issue')
            ->orderByDesc('certificate_id')
            ->get()
            ->unique('resident_id')
            ->keyBy('resident_id');
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

        if ($soloParent === null) {
            $existing = CertificateSoloParent::query()->where('certificate_id', $certificateId)->first();

            if ($existing !== null) {
                CertificateSoloParentChild::query()
                    ->where('certificate_solo_parent_id', $existing->certificate_solo_parent_id)
                    ->delete();
                $existing->delete();
            }

            return;
        }

        $record = CertificateSoloParent::query()->updateOrCreate(
            ['certificate_id' => $certificateId],
            [
                'certificate_solo_parent_id' => $certificateId,
                'social_service_kagawad_id' => $soloParent['social_service_kagawad_id'],
                'president_name_id' => $soloParent['president_name_id'],
                'president_address' => $soloParent['president_address'],
                'neighbor_name' => $soloParent['neighbor_name'],
                'neighbor_address' => $soloParent['neighbor_address'],
                'circumstance' => $soloParent['circumstance'],
            ],
        );

        CertificateSoloParentChild::query()
            ->where('certificate_solo_parent_id', $record->certificate_solo_parent_id)
            ->delete();

        foreach ($soloParent['children'] as $child) {
            CertificateSoloParentChild::query()->create([
                'certificate_solo_parent_id' => $record->certificate_solo_parent_id,
                'child_name' => $child['child_name'],
                'date_of_birth' => $child['date_of_birth'],
            ]);
        }
    }

    /**
     * @param  array{ethnicity_id: int, date_of_death: string, place_of_death: string, cause_of_death: string}|null  $death
     */
    private function syncDeath(Certificate $certificate, ?array $death): void
    {
        $certificateId = (int) $certificate->certificate_id;

        if ($death === null) {
            DeathCertificate::query()->where('certificate_id', $certificateId)->delete();

            return;
        }

        DeathCertificate::query()->updateOrCreate(
            ['certificate_id' => $certificateId],
            $death,
        );
    }

    /**
     * @param  array{include_income_amount: int, stated_income_amount: ?string, resident_since: ?string}|null  $lowIncome
     */
    private function syncLowIncome(Certificate $certificate, ?array $lowIncome): void
    {
        $certificateId = (int) $certificate->certificate_id;

        if ($lowIncome === null) {
            LowIncomeCertificate::query()->where('certificate_id', $certificateId)->delete();

            return;
        }

        LowIncomeCertificate::query()->updateOrCreate(
            ['certificate_id' => $certificateId],
            $lowIncome,
        );
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
