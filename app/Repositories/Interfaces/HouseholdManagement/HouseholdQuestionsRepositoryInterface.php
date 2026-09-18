<?php

namespace App\Repositories\Interfaces\HouseholdManagement;

use App\Models\HouseholdManagement\HouseholdQuestions;

interface HouseholdQuestionsRepositoryInterface
{
    public function findById(int $householdQuestionsId): ?HouseholdQuestions;

    public function findByHouseholdId(int $householdId): ?HouseholdQuestions;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): HouseholdQuestions;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(HouseholdQuestions $questions, array $attributes): HouseholdQuestions;
}
