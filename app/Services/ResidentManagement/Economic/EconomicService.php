<?php

namespace App\Services\ResidentManagement\Economic;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Economic\Economic;
use App\Models\ResidentManagement\Economic\SourceOfIncome;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Economic\EconomicRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EconomicService
{
    use LogsAuditableFieldChanges;

    public function __construct(
        protected EconomicRepositoryInterface $economicRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(User $performedBy, Resident $resident, array $data): array
    {
        if ($this->economicRepository->findByResidentId($resident->resident_id) !== null) {
            throw ValidationException::withMessages([
                'economic' => ['An economic record already exists for this resident.'],
            ]);
        }

        $data['resident_id'] = $resident->resident_id;
        $data = $this->applyWorkDetailRules($data);

        return DB::transaction(function () use ($performedBy, $data) {
            $economic = $this->economicRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $economic->economic_id,
                description: 'Create economic',
                oldValue: null,
                newValue: (string) $economic->monthly_income,
                target: 'record',
                entity: 'economic',
            );

            return $this->formatRecord($economic);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $performedBy, Economic $economic, array $data): array
    {
        $previous = $this->auditSnapshot($economic);
        $data = $this->applyWorkDetailRules(array_merge($economic->only([
            'monthly_income',
            'source_of_income_id',
            'status_of_work_business_id',
            'place_of_work_business',
        ]), $data));

        return DB::transaction(function () use ($performedBy, $economic, $data, $previous) {
            $updated = $this->economicRepository->update($economic, $data);

            $this->logFieldChanges(
                $performedBy,
                $updated->economic_id,
                'economic',
                $previous,
                $this->auditSnapshot($updated),
                'Updated economic',
            );

            return $this->formatRecord($updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Economic $economic): array
    {
        $economic->loadMissing(['sourceOfIncome', 'statusOfWorkBusiness']);

        return [
            'economic_id' => $economic->economic_id,
            'resident_id' => $economic->resident_id,
            'monthly_income' => $economic->monthly_income,
            'source_of_income_id' => $economic->source_of_income_id,
            'source_of_income' => $economic->sourceOfIncome?->source_of_income,
            'status_of_work_business_id' => $economic->status_of_work_business_id === Economic::STATUS_NOT_APPLICABLE
                ? null
                : $economic->status_of_work_business_id,
            'status_of_work_business' => $economic->status_of_work_business_id === Economic::STATUS_NOT_APPLICABLE
                ? null
                : $economic->statusOfWorkBusiness?->status_of_work_business,
            'place_of_work_business' => $economic->place_of_work_business,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function auditSnapshot(Economic $economic): array
    {
        $economic->loadMissing(['sourceOfIncome', 'statusOfWorkBusiness']);

        return [
            'monthly income' => (string) $economic->monthly_income,
            'source of income' => (string) ($economic->sourceOfIncome?->source_of_income ?? $economic->source_of_income_id),
            'status of work' => $economic->status_of_work_business_id === Economic::STATUS_NOT_APPLICABLE
                ? 'N/A'
                : (string) ($economic->statusOfWorkBusiness?->status_of_work_business ?? $economic->status_of_work_business_id),
            'place of work' => (string) ($economic->place_of_work_business ?? ''),
        ];
    }

    /**
     * Remittance, investments, and others skip Q17–Q18.
     * status_of_work_business_id is INT NOT NULL, so 0 is stored as N/A.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyWorkDetailRules(array $data): array
    {
        if (! SourceOfIncome::skipsWorkDetails($data['source_of_income_id'] ?? null)) {
            return $data;
        }

        $data['status_of_work_business_id'] = Economic::STATUS_NOT_APPLICABLE;
        $data['place_of_work_business'] = null;

        return $data;
    }
}
