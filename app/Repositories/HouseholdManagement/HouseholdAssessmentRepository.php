<?php

namespace App\Repositories\HouseholdManagement;

use App\Models\HouseholdManagement\HouseholdAssessment;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdAssessmentRepositoryInterface;

class HouseholdAssessmentRepository implements HouseholdAssessmentRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): HouseholdAssessment
    {
        $assessment = HouseholdAssessment::create($attributes);

        return $assessment->load(['censusStatus', 'encoder', 'interviewer', 'supervisor']);
    }
}
