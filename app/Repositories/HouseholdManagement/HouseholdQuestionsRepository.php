<?php

namespace App\Repositories\HouseholdManagement;

use App\Models\HouseholdManagement\HouseholdQuestions;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdQuestionsRepositoryInterface;

class HouseholdQuestionsRepository implements HouseholdQuestionsRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return [
            'ownershipOfHousingUnit',
            'ownershipOfLot',
            'fuelTypeForLighting',
            'fuelTypeForCooking',
            'mainSourceDrinkingWater',
            'kitchenGarbageDisposal',
            'toiletFacilityType',
            'typeOfBuildingHouse',
            'constructionMaterialOuterWall',
            'commonDiseases',
            'primaryNeeds',
            'intendToStay',
        ];
    }

    public function findById(int $householdQuestionsId): ?HouseholdQuestions
    {
        return HouseholdQuestions::query()
            ->with($this->defaultRelations())
            ->where('household_questions_id', $householdQuestionsId)
            ->first();
    }

    public function findByHouseholdId(int $householdId): ?HouseholdQuestions
    {
        return HouseholdQuestions::query()
            ->with($this->defaultRelations())
            ->where('household_id', $householdId)
            ->orderByDesc('household_questions_id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): HouseholdQuestions
    {
        $questions = HouseholdQuestions::create($attributes);

        return $questions->load($this->defaultRelations());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(HouseholdQuestions $questions, array $attributes): HouseholdQuestions
    {
        $questions->fill($attributes);
        $questions->save();

        return $questions->fresh($this->defaultRelations()) ?? $questions;
    }
}
