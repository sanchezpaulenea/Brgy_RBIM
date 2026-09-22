<?php

namespace App\Services\HouseholdManagement;

use App\Models\HouseholdManagement\CommonDiseaseCauseDeathInBrgy;
use App\Models\HouseholdManagement\Household;
use App\Models\HouseholdManagement\HouseholdQuestions;
use App\Models\HouseholdManagement\PrimaryNeedOfBrgy;
use App\Models\Logs\Action;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdQuestionsRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HouseholdQuestionsService
{
    use LogsAuditableFieldChanges;

    public function __construct(
        protected HouseholdQuestionsRepositoryInterface $householdQuestionsRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(User $performedBy, Household $household, array $data): array
    {
        if ($this->householdQuestionsRepository->findByHouseholdId($household->household_id) !== null) {
            throw ValidationException::withMessages([
                'questions' => ['Household questions already exist for this household.'],
            ]);
        }

        return DB::transaction(function () use ($performedBy, $household, $data) {
            $questions = $this->householdQuestionsRepository->create(
                $this->persistableAttributes($data, $household->household_id),
            );

            $this->syncRelatedRecords($questions, $data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $questions->household_questions_id,
                description: 'Create household questions',
                oldValue: null,
                newValue: 'Household '.$household->household_id,
                target: 'record',
                entity: 'household_questions',
            );

            return $this->formatRecord(
                $this->householdQuestionsRepository->findById($questions->household_questions_id) ?? $questions,
            );
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $performedBy, HouseholdQuestions $questions, array $data): array
    {
        $previous = $this->auditSnapshot($questions);

        return DB::transaction(function () use ($performedBy, $questions, $data, $previous) {
            $updated = $this->householdQuestionsRepository->update(
                $questions,
                $this->persistableAttributes($data),
            );

            $this->syncRelatedRecords(
                $updated,
                $data,
                array_key_exists('common_diseases', $data),
                array_key_exists('primary_needs', $data),
                $this->hasIntendToStayInput($data),
                $this->hasFemaleDeathInput($data),
                $this->hasChildDeathInput($data),
            );

            $fresh = $this->householdQuestionsRepository->findById($updated->household_questions_id) ?? $updated;

            $this->logFieldChanges(
                $performedBy,
                $fresh->household_questions_id,
                'household_questions',
                $previous,
                $this->auditSnapshot($fresh),
                'Updated household questions',
            );

            return $this->formatRecord($fresh);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(HouseholdQuestions $questions): array
    {
        $questions->loadMissing([
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
            'femaleDeaths',
            'childDeaths.sex',
        ]);

        return [
            'household_questions_id' => $questions->household_questions_id,
            'household_id' => $questions->household_id,
            'ownership_of_housing_unit_id' => $questions->ownership_of_housing_unit_id,
            'ownership_of_housing_unit' => $questions->ownershipOfHousingUnit?->ownership_type,
            'ownership_of_lot_id' => $questions->ownership_of_lot_id,
            'ownership_of_lot' => $questions->ownershipOfLot?->ownership_type,
            'fuel_type_for_lighting_id' => $questions->fuel_type_for_lighting_id,
            'fuel_type_for_lighting' => $questions->fuelTypeForLighting?->fuel_type,
            'fuel_type_for_cooking_id' => $questions->fuel_type_for_cooking_id,
            'fuel_type_for_cooking' => $questions->fuelTypeForCooking?->fuel_type,
            'main_source_drinking_water_id' => $questions->main_source_drinking_water_id,
            'main_source_drinking_water' => $questions->mainSourceDrinkingWater?->water_source_id,
            'kitchen_garbage_disposal_id' => $questions->kitchen_garbage_disposal_id,
            'kitchen_garbage_disposal' => $questions->kitchenGarbageDisposal?->kitchen_garbage_disposal,
            'perform_garbage_seggragation' => $questions->perform_garbage_seggragation,
            'toilet_facility_type_id' => $questions->toilet_facility_type_id,
            'toilet_facility_type' => $questions->toiletFacilityType?->toilet_facility_type,
            'type_of_building_house_id' => $questions->type_of_building_house_id,
            'type_of_building_house' => $questions->typeOfBuildingHouse?->building_house_type,
            'construction_material_outer_wall_id' => $questions->construction_material_outer_wall_id,
            'construction_material_outer_wall' => $questions->constructionMaterialOuterWall?->construction_material_outer_wall,
            'female_hhm_died_past_12mos' => $questions->femaleDeaths->isNotEmpty(),
            'female_deaths' => $questions->femaleDeaths
                ->map(fn ($death) => [
                    'female_hhm_died_id' => $death->female_hhm_died_id,
                    'age' => $death->age,
                    'cause_of_death' => $death->cause_of_death,
                ])
                ->values()
                ->all(),
            'child_hhm_died_past_12mos' => $questions->childDeaths->isNotEmpty(),
            'child_deaths' => $questions->childDeaths
                ->map(fn ($death) => [
                    'child_hhm_died_id' => $death->child_hhm_died_id,
                    'age' => $death->age,
                    'cause_of_death' => $death->cause_of_death,
                    'sex_id' => $death->sex_id,
                    'sex' => $death->sex?->sex,
                ])
                ->values()
                ->all(),
            'common_diseases' => $questions->commonDiseases
                ->pluck('common_disease')
                ->filter()
                ->values()
                ->all(),
            'primary_needs' => $questions->primaryNeeds
                ->pluck('primary_need')
                ->filter()
                ->values()
                ->all(),
            'intend_to_stay_brgy' => $questions->intendToStay?->intend_to_stay_brgy,
            'intend_to_stay_municipality' => $questions->intendToStay?->intend_to_stay_municipality,
            'intend_to_stay_province' => $questions->intendToStay?->intend_to_stay_province,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function persistableAttributes(array $data, ?int $householdId = null): array
    {
        $attributes = array_intersect_key($data, array_flip([
            'ownership_of_housing_unit_id',
            'ownership_of_lot_id',
            'fuel_type_for_lighting_id',
            'fuel_type_for_cooking_id',
            'main_source_drinking_water_id',
            'kitchen_garbage_disposal_id',
            'perform_garbage_seggragation',
            'toilet_facility_type_id',
            'type_of_building_house_id',
            'construction_material_outer_wall_id',
        ]));

        if ($householdId !== null) {
            $attributes['household_id'] = $householdId;
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncRelatedRecords(
        HouseholdQuestions $questions,
        array $data,
        bool $syncDiseases = true,
        bool $syncNeeds = true,
        bool $syncIntendToStay = true,
        bool $syncFemaleDeaths = true,
        bool $syncChildDeaths = true,
    ): void {
        if ($syncDiseases) {
            $questions->commonDiseases()->sync(
                $this->namedLookupIds(
                    $data['common_diseases'] ?? [],
                    CommonDiseaseCauseDeathInBrgy::class,
                    'common_disease',
                ),
            );
        }

        if ($syncNeeds) {
            $questions->primaryNeeds()->sync(
                $this->namedLookupIds(
                    $data['primary_needs'] ?? [],
                    PrimaryNeedOfBrgy::class,
                    'primary_need',
                ),
            );
        }

        if ($syncIntendToStay && $this->hasIntendToStayInput($data)) {
            $questions->intendToStay()->updateOrCreate(
                ['household_question_id' => $questions->household_questions_id],
                [
                    'intend_to_stay_brgy' => $data['intend_to_stay_brgy'] ?? $questions->intendToStay?->intend_to_stay_brgy,
                    'intend_to_stay_municipality' => $data['intend_to_stay_municipality'] ?? $questions->intendToStay?->intend_to_stay_municipality,
                    'intend_to_stay_province' => $data['intend_to_stay_province'] ?? $questions->intendToStay?->intend_to_stay_province,
                ],
            );
        }

        if ($syncFemaleDeaths) {
            $this->syncFemaleDeaths($questions, $data);
        }

        if ($syncChildDeaths) {
            $this->syncChildDeaths($questions, $data);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncFemaleDeaths(HouseholdQuestions $questions, array $data): void
    {
        $questions->femaleDeaths()->delete();

        if (! $this->answeredYes($data['female_hhm_died_past_12mos'] ?? null)) {
            return;
        }

        foreach ($data['female_deaths'] ?? [] as $death) {
            if (! is_array($death)) {
                continue;
            }

            $questions->femaleDeaths()->create([
                'age' => (int) $death['age'],
                'cause_of_death' => $death['cause_of_death'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncChildDeaths(HouseholdQuestions $questions, array $data): void
    {
        $questions->childDeaths()->delete();

        if (! $this->answeredYes($data['child_hhm_died_past_12mos'] ?? null)) {
            return;
        }

        foreach ($data['child_deaths'] ?? [] as $death) {
            if (! is_array($death)) {
                continue;
            }

            $questions->childDeaths()->create([
                'age' => (int) $death['age'],
                'cause_of_death' => $death['cause_of_death'],
                'sex_id' => (int) $death['sex_id'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hasFemaleDeathInput(array $data): bool
    {
        return array_key_exists('female_hhm_died_past_12mos', $data)
            || array_key_exists('female_deaths', $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hasChildDeathInput(array $data): bool
    {
        return array_key_exists('child_hhm_died_past_12mos', $data)
            || array_key_exists('child_deaths', $data);
    }

    private function answeredYes(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1' || $value === 'true';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hasIntendToStayInput(array $data): bool
    {
        return array_key_exists('intend_to_stay_brgy', $data)
            || array_key_exists('intend_to_stay_municipality', $data)
            || array_key_exists('intend_to_stay_province', $data);
    }

    /**
     * @param  list<mixed>  $names
     * @param  class-string<Model>  $modelClass
     * @return list<int>
     */
    private function namedLookupIds(array $names, string $modelClass, string $column): array
    {
        $ids = [];

        foreach ($names as $name) {
            if (! is_string($name)) {
                continue;
            }

            $normalized = Str::of($name)->squish()->title()->toString();

            if ($normalized === '') {
                continue;
            }

            if (! in_array($column, ['common_disease', 'primary_need'], true)) {
                throw new \InvalidArgumentException('Unsupported household question lookup column.');
            }

            $existing = $modelClass::query()
                ->whereRaw('LOWER('.$column.') = ?', [Str::lower($normalized)])
                ->first();

            if ($existing === null) {
                $existing = $modelClass::query()->create([
                    $column => $normalized,
                ]);
            }

            $ids[] = (int) $existing->getKey();
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array<string, string>
     */
    private function auditSnapshot(HouseholdQuestions $questions): array
    {
        $formatted = $this->formatRecord($questions);

        return [
            'housing unit ownership' => (string) ($formatted['ownership_of_housing_unit'] ?? ''),
            'lot ownership' => (string) ($formatted['ownership_of_lot'] ?? ''),
            'lighting fuel' => (string) ($formatted['fuel_type_for_lighting'] ?? ''),
            'cooking fuel' => (string) ($formatted['fuel_type_for_cooking'] ?? ''),
            'drinking water' => (string) ($formatted['main_source_drinking_water'] ?? ''),
            'garbage disposal' => (string) ($formatted['kitchen_garbage_disposal'] ?? ''),
            'garbage segregation' => $this->yesNo($formatted['perform_garbage_seggragation'] ?? null),
            'toilet facility' => (string) ($formatted['toilet_facility_type'] ?? ''),
            'building type' => (string) ($formatted['type_of_building_house'] ?? ''),
            'outer wall' => (string) ($formatted['construction_material_outer_wall'] ?? ''),
            'female death past 12 months' => $this->deathAuditLabel($formatted['female_deaths'] ?? []),
            'child death past 12 months' => $this->deathAuditLabel($formatted['child_deaths'] ?? [], includeSex: true),
            'common diseases' => implode(', ', $formatted['common_diseases'] ?? []),
            'primary needs' => implode(', ', $formatted['primary_needs'] ?? []),
            'intend to stay' => trim(implode(', ', array_filter([
                $formatted['intend_to_stay_brgy'] ?? null,
                $formatted['intend_to_stay_municipality'] ?? null,
                $formatted['intend_to_stay_province'] ?? null,
            ]))),
        ];
    }

    private function yesNo(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return $value ? 'Yes' : 'No';
    }

    /**
     * @param  list<array<string, mixed>>  $deaths
     */
    private function deathAuditLabel(array $deaths, bool $includeSex = false): string
    {
        if ($deaths === []) {
            return 'No';
        }

        $details = array_map(function (array $death) use ($includeSex): string {
            $parts = array_filter([
                $includeSex ? ($death['sex'] ?? null) : null,
                isset($death['age']) ? $death['age'].' yrs' : null,
                $death['cause_of_death'] ?? null,
            ]);

            return implode(', ', $parts);
        }, $deaths);

        return 'Yes — '.implode('; ', array_filter($details));
    }
}
