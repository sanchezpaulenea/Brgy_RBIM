<?php

namespace App\Repositories\HouseholdManagement;

use App\Models\HouseholdManagement\HouseholdAssessment;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdAssessmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class HouseholdAssessmentRepository implements HouseholdAssessmentRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function relations(): array
    {
        return [
            'censusStatus',
            'encoder',
            'interviewer',
            'supervisor',
            'previousAssessment.censusStatus',
            'household.street',
            'household.head',
            'household.clan',
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): HouseholdAssessment
    {
        $assessment = HouseholdAssessment::create($attributes);

        return $assessment->load($this->relations());
    }

    /**
     * @return Collection<int, HouseholdAssessment>
     */
    public function listAll(): Collection
    {
        return HouseholdAssessment::query()
            ->with($this->relations())
            ->orderByDesc('assessment_id')
            ->get();
    }

    /**
     * @return Collection<int, HouseholdAssessment>
     */
    public function listByHousehold(int $householdId): Collection
    {
        return HouseholdAssessment::query()
            ->with($this->relations())
            ->where('household_id', $householdId)
            ->orderByDesc('assessment_id')
            ->get();
    }

    public function latestByHousehold(int $householdId): ?HouseholdAssessment
    {
        return HouseholdAssessment::query()
            ->with($this->relations())
            ->where('household_id', $householdId)
            ->orderByDesc('assessment_id')
            ->first();
    }
}
