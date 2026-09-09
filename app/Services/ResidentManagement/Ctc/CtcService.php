<?php

namespace App\Services\ResidentManagement\Ctc;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Ctc\Ctc;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Ctc\CtcRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CtcService
{
    use LogsAuditableFieldChanges;

    public function __construct(
        protected CtcRepositoryInterface $ctcRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(User $performedBy, Resident $resident, array $data): array
    {
        $this->assertAdult($resident);

        if ($this->ctcRepository->findByResidentId($resident->resident_id) !== null) {
            throw ValidationException::withMessages([
                'ctc' => ['A community tax certificate record already exists for this resident.'],
            ]);
        }

        $data['resident_id'] = $resident->resident_id;
        $data = $this->applyIssuedHereSkip($data);

        return DB::transaction(function () use ($performedBy, $data) {
            $ctc = $this->ctcRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $ctc->community_tax_cert,
                description: 'Create community tax cert',
                oldValue: null,
                newValue: $ctc->has_valid_ctc ? 'Yes' : 'No',
                target: 'record',
                entity: 'community_tax_cert',
            );

            return $this->formatRecord($ctc);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $performedBy, Ctc $ctc, array $data): array
    {
        $ctc->loadMissing('resident');
        $this->assertAdult($ctc->resident);

        $previous = $this->auditSnapshot($ctc);
        $data = $this->applyIssuedHereSkip(array_merge($ctc->only([
            'has_valid_ctc',
            'ctc_issued_here',
        ]), $data));

        return DB::transaction(function () use ($performedBy, $ctc, $data, $previous) {
            $updated = $this->ctcRepository->update($ctc, $data);

            $this->logFieldChanges(
                $performedBy,
                $updated->community_tax_cert,
                'community_tax_cert',
                $previous,
                $this->auditSnapshot($updated),
                'Updated community tax cert',
            );

            return $this->formatRecord($updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Ctc $ctc): array
    {
        return [
            'community_tax_cert' => $ctc->community_tax_cert,
            'resident_id' => $ctc->resident_id,
            'has_valid_ctc' => (bool) $ctc->has_valid_ctc,
            'ctc_issued_here' => $ctc->has_valid_ctc ? (bool) $ctc->ctc_issued_here : false,
        ];
    }

    /**
     * Q42B is skipped when Q42A is no. TINYINT NOT NULL stores 0.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyIssuedHereSkip(array $data): array
    {
        if (! filter_var($data['has_valid_ctc'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $data['has_valid_ctc'] = false;
            $data['ctc_issued_here'] = false;
        } elseif (! array_key_exists('ctc_issued_here', $data) || $data['ctc_issued_here'] === null || $data['ctc_issued_here'] === '') {
            $data['ctc_issued_here'] = false;
        }

        return $data;
    }

    private function assertAdult(?Resident $resident): void
    {
        if ($resident === null || ! $resident->canHaveCtc()) {
            throw ValidationException::withMessages([
                'resident_id' => ['Community tax certificate applies only to residents aged 18 and above.'],
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function auditSnapshot(Ctc $ctc): array
    {
        return [
            'has valid ctc' => $ctc->has_valid_ctc ? 'Yes' : 'No',
            'ctc issued here' => $ctc->ctc_issued_here ? 'Yes' : 'No',
        ];
    }
}
