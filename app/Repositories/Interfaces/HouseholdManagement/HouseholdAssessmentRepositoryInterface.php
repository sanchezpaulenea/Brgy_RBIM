<?php

namespace App\Repositories\Interfaces\HouseholdManagement;

use App\Models\HouseholdManagement\HouseholdAssessment;
use Illuminate\Database\Eloquent\Collection;

interface HouseholdAssessmentRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): HouseholdAssessment;

    /**
     * @return Collection<int, HouseholdAssessment>
     */
    public function listAll(): Collection;

    /**
     * @return Collection<int, HouseholdAssessment>
     */
    public function listByHousehold(int $householdId): Collection;

    public function latestByHousehold(int $householdId): ?HouseholdAssessment;

    public function lockById(int $assessmentId): ?HouseholdAssessment;

    public function updateStatus(HouseholdAssessment $assessment, int $censusStatusId): HouseholdAssessment;
}
