<?php

namespace App\Services;

use App\Repositories\Interfaces\DashboardRepositoryInterface;

class DashboardService
{
    /**
     * Ranked slices kept before the rest fold into "Others". A category with
     * one value more than this keeps every slice, since an "Others" of one
     * value would hide a name for no gain.
     */
    public const TOP_SLICES = 5;

    private const HOUSEHOLDS = 'Total Household Records';

    private const RESIDENTS = 'Total Resident Records';

    private const NOT_ANSWERED = 'Not Answered';

    private const NOT_SPECIFIED = 'Not Specified';

    /**
     * @var list<array{label: string, min: int, max: int}>
     */
    private const AGE_BUCKETS = [
        ['label' => '0–5', 'min' => 0, 'max' => 5],
        ['label' => '6–12', 'min' => 6, 'max' => 12],
        ['label' => '13–17', 'min' => 13, 'max' => 17],
        ['label' => '18–24', 'min' => 18, 'max' => 24],
        ['label' => '25–59', 'min' => 25, 'max' => 59],
        ['label' => '60+', 'min' => 60, 'max' => 150],
    ];

    /**
     * @var list<array{label: string, min: int|float|null, max: int|float|null}>
     */
    private const INCOME_BUCKETS = [
        ['label' => 'Below ₱10,000', 'min' => 0, 'max' => 9999.99],
        ['label' => '₱10,000 – ₱19,999', 'min' => 10000, 'max' => 19999.99],
        ['label' => '₱20,000 – ₱39,999', 'min' => 20000, 'max' => 39999.99],
        ['label' => '₱40,000 – ₱59,999', 'min' => 40000, 'max' => 59999.99],
        ['label' => '₱60,000 and above', 'min' => 60000, 'max' => null],
    ];

    /**
     * @var list<array{label: string, min: int|float|null, max: int|float|null}>
     */
    private const PREGNANCY_BUCKETS = [
        ['label' => '0', 'min' => 0, 'max' => 0],
        ['label' => '1', 'min' => 1, 'max' => 1],
        ['label' => '2', 'min' => 2, 'max' => 2],
        ['label' => '3–4', 'min' => 3, 'max' => 4],
        ['label' => '5+', 'min' => 5, 'max' => null],
    ];

    /**
     * @var list<array{label: string, min: int|float|null, max: int|float|null}>
     */
    private const LIVING_CHILDREN_BUCKETS = [
        ['label' => '0', 'min' => 0, 'max' => 0],
        ['label' => '1', 'min' => 1, 'max' => 1],
        ['label' => '2', 'min' => 2, 'max' => 2],
        ['label' => '3–4', 'min' => 3, 'max' => 4],
        ['label' => '5+', 'min' => 5, 'max' => null],
    ];

    public function __construct(
        protected DashboardRepositoryInterface $dashboardRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function statistics(): array
    {
        $repo = $this->dashboardRepository;
        $womenVia = [
            'table' => 'health',
            'from' => 'health_id',
            'to' => 'health_id',
            'owner' => 'resident_id',
        ];

        return [
            'summary' => $repo->summaryCounts(),
            'charts' => [
                'clan_households' => $this->chart(self::HOUSEHOLDS, $repo->householdsByLookup('clan_id', 'clan', 'clan_id', 'clan_name')),
                'clan_residents' => $this->chart(self::RESIDENTS, $repo->residentsByLookup('clan_id', 'clan', 'clan_id', 'clan_name')),
                'street' => $this->chart(self::HOUSEHOLDS, $repo->householdsByLookup('street_id', 'street', 'street_id', 'street_name')),
                'household_status' => $this->chart(self::HOUSEHOLDS, $repo->householdsByLookup('household_status_id', 'household_status', 'household_status_id', 'household_status')),
                'house_story' => $this->chart(self::HOUSEHOLDS, $this->storyRows($repo->householdsByNumber('number_of_house_story'))),
                'basement_level' => $this->chart(self::HOUSEHOLDS, $this->basementRows($repo->householdsByNumber('number_of_basement_level', 0))),

                'ownership' => $this->chart(self::HOUSEHOLDS, $this->ownershipRows($repo->householdsByOwnership()), self::NOT_ANSWERED),
                'ownership_unit' => $this->questionChart('ownership_of_housing_unit_id', 'ownership_type', 'ownership_type_id', 'ownership_type'),
                'ownership_lot' => $this->questionChart('ownership_of_lot_id', 'ownership_type', 'ownership_type_id', 'ownership_type'),
                'fuel_lighting' => $this->questionChart('fuel_type_for_lighting_id', 'fuel_type', 'fuel_type_id', 'fuel_type'),
                'fuel_cooking' => $this->questionChart('fuel_type_for_cooking_id', 'fuel_type', 'fuel_type_id', 'fuel_type'),
                'water_source' => $this->questionChart('main_source_drinking_water_id', 'water_source', 'water_source_id', 'water_source'),
                'kitchen_garbage' => $this->questionChart('kitchen_garbage_disposal_id', 'kitchen_garbage_disposal', 'kitchen_garbage_disposal_id', 'kitchen_garbage_disposal'),
                'segregate_garbage' => $this->booleanChart(
                    self::HOUSEHOLDS,
                    $repo->householdsByQuestionBoolean('perform_garbage_seggragation'),
                    'Segregates',
                    'Does Not Segregate',
                ),
                'toilet_facility' => $this->questionChart('toilet_facility_type_id', 'toilet_facility_type', 'toilet_facility_type_id', 'toilet_facility_type'),
                'building_type' => $this->questionChart('type_of_building_house_id', 'building_house_type', 'building_house_type_id', 'building_house_type'),
                'construction_material' => $this->questionChart('construction_material_outer_wall_id', 'construction_material_outer_wall', 'construction_material_outer_wall_id', 'construction_material_outer_wall'),

                'female_died' => $this->booleanChart(
                    self::HOUSEHOLDS,
                    $repo->householdsByPresence('female_hhm_died', 'household_question_id'),
                    'Have Female Household Member Died in the Past 12 Months',
                    'Does Not Have Female Household Member Died in the Past 12 Months',
                ),
                'child_died' => $this->booleanChart(
                    self::HOUSEHOLDS,
                    $repo->householdsByPresence('child_hhm_died', 'household_question_id'),
                    'Have Child Below 5 Household Member Died in the Past 12 Months',
                    'Does Not Have Child Below 5 Household Member Died in the Past 12 Months',
                ),
                'common_disease' => $this->chart('Total Diseases Recorded', $repo->questionJunctionCounts('common_disease', 'common_disease_id', 'common_disease_cause_death_in_brgy', 'common_disease_id', 'common_disease')),
                'primary_need' => $this->chart('Total Primary Needs Recorded', $repo->questionJunctionCounts('primary_need', 'primary_need_id', 'primary_need_of_brgy', 'primary_need_id', 'primary_need')),
                'pets' => $this->chart('Total Pets Recorded', $repo->petsByLookup('specie_id', 'specie', 'specie_id', 'specie')),
                'pets_sex' => $this->chart('Total Pets Recorded', $repo->petsByLookup('sex_id', 'sex', 'sex_id', 'sex')),
                'pets_rabies' => $this->chart('Total Pets Recorded', $repo->petsByRabies()),
                'pets_spay' => $this->booleanChart(
                    'Total Pets Recorded',
                    $repo->petsByBoolean('is_spay_neuter'),
                    'Spayed / Neutered',
                    'Not Spayed / Neutered',
                ),

                'relationship_to_hh' => $this->chart(self::RESIDENTS, $repo->residentsByLookup('relationship_to_hh_id', 'relationship_to_hh', 'relationship_to_hh_id', 'relationship_to_hh')),
                'gender' => $this->chart(self::RESIDENTS, $repo->residentsByLookup('sex_id', 'sex', 'sex_id', 'sex')),
                'nationality' => $this->chart(self::RESIDENTS, $repo->residentsByLookup('nationality_id', 'nationality', 'nationality_id', 'nationality')),
                'religion' => $this->chart(self::RESIDENTS, $repo->residentsByLookup('religion_id', 'religion', 'religion_id', 'religion')),
                'ethnicity' => $this->chart(self::RESIDENTS, $repo->residentsByLookup('ethnicity_id', 'ethnicity', 'ethnicity_id', 'ethnicity')),
                'marital_status' => $this->chart(self::RESIDENTS, $repo->residentsByLookup('marital_status_id', 'marital_status', 'marital_status_id', 'marital_status')),
                'resident_status' => $this->chart(self::RESIDENTS, $repo->residentsByLookup('resident_status_id', 'resident_status', 'resident_status_id', 'resident_status')),
                'education' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('education', 'highest_lvl_of_educ_id', 'highest_lvl_of_educ', 'highest_lvl_of_educ_id', 'lvl_of_educ'), self::NOT_ANSWERED),
                'education_enrollment' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('education', 'current_enrollement_status_id', 'current_enrollment_status', 'current_enrollment_status_id', 'current_enrollement_status'), self::NOT_ANSWERED),
                'economic' => $this->chart(self::RESIDENTS, $repo->residentsByChildNumberBuckets('economic', 'monthly_income', self::INCOME_BUCKETS), self::NOT_ANSWERED),
                'economic_source' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('economic', 'source_of_income_id', 'source_of_income', 'source_of_income_id', 'source_of_income'), self::NOT_ANSWERED),
                'economic_work_status' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('economic', 'status_of_work_business_id', 'status_of_work_business', 'status_of_work_business_id', 'status_of_work_business'), self::NOT_ANSWERED),
                'health' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('health', 'health_insurance_id', 'health_insurance', 'health_insurance_id', 'health_insurance'), self::NOT_ANSWERED),
                'health_facility' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('health', 'facility_visited_past_12mos_id', 'facility_visited_past_12mos', 'facility_visited_past_12mos_id', 'facility_visited_past_12mos'), self::NOT_ANSWERED),
                'health_reason' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('health', 'facility_visit_reason_id', 'facility_visit_reason', 'facility_visit_reason_id', 'facility_visit_reason'), self::NOT_ANSWERED),
                'health_disability' => $this->booleanChart(
                    self::RESIDENTS,
                    $repo->residentsByDisability(),
                    'With Disability',
                    'Without Disability',
                ),
                'infant_health' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('infant_health', 'place_of_delivery_id', 'place_of_delivery', 'place_of_delivery_id', 'place_of_delivery', 'infant'), self::NOT_ANSWERED),
                'infant_attendant' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('infant_health', 'birth_attendant_id', 'birth_attendant', 'birth_attendant_id', 'birth_attendant', 'infant'), self::NOT_ANSWERED),
                'infant_immunization' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('infant_health', 'immunization_id', 'immunization', 'immunization_id', 'immunization', 'infant'), self::NOT_ANSWERED),
                'women_health' => $this->chart(self::RESIDENTS, $repo->residentsByChildNumberBuckets('women_health', 'number_pregnancies', self::PREGNANCY_BUCKETS, 'women', $womenVia), self::NOT_ANSWERED),
                'women_living_children' => $this->chart(self::RESIDENTS, $repo->residentsByChildNumberBuckets('women_health', 'living_children', self::LIVING_CHILDREN_BUCKETS, 'women', $womenVia), self::NOT_ANSWERED),
                'women_fp' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('women_health', 'family_planning_method_id', 'family_planning_method', 'family_planning_method_id', 'family_planning_method', 'women', $womenVia), self::NOT_ANSWERED),
                'women_fp_source' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('women_health', 'source_of_fp_method_id', 'source_of_fp_method', 'source_of_fp_method_id', 'source_of_fp_method', 'women', $womenVia), self::NOT_ANSWERED),
                'women_fp_intention' => $this->chart(self::RESIDENTS, $repo->residentsByTriState('women_health', 'have_intention_to_use_fp', 'women', $womenVia), self::NOT_ANSWERED),
                'solo_parent_status' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('sociocivic', 'solo_parent_status_id', 'solo_parent_status', 'solo_parent_status_id', 'solo_parent_status'), self::NOT_ANSWERED),
                'registered_senior' => $this->booleanChart(
                    self::RESIDENTS,
                    $repo->residentsByColumnPresence('sociocivic', 'osca_id_number'),
                    'Registered Senior Citizen',
                    'Not a Registered Senior Citizen',
                ),
                'registered_voter' => $this->chart(self::RESIDENTS, $repo->residentsByVoterStatus(), self::NOT_ANSWERED),
                'migration' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('migration', 'resident_type_id', 'resident_type', 'resident_type_id', 'resident_type'), self::NOT_ANSWERED),
                'ctc' => $this->chart(self::RESIDENTS, $repo->residentsByCtcStatus(), self::NOT_ANSWERED),
                'skills_training' => $this->chart(self::RESIDENTS, $repo->residentsByChildDistinct('skills_development', 'skills_development_training', 'skills_development_id', 'skills'), self::NOT_ANSWERED),
                'skills' => $this->chart(self::RESIDENTS, $repo->residentsByChildLookup('skills_development', 'skill_type_id', 'skill_type', 'skill_type_id', 'skill_type', 'skills'), self::NOT_ANSWERED),
                'age' => $this->chart(self::RESIDENTS, $repo->residentsByAgeBuckets(self::AGE_BUCKETS)),
                'age_disability' => $this->booleanChart(
                    self::RESIDENTS,
                    $repo->residentsByDisability(),
                    'With Disability',
                    'Without Disability',
                ),
            ],
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function questionChart(string $column, string $lookupTable, string $lookupId, string $lookupLabel): array
    {
        $rows = $this->dashboardRepository->householdsByQuestionLookup($column, $lookupTable, $lookupId, $lookupLabel);

        return $this->chart(self::HOUSEHOLDS, $rows, self::NOT_ANSWERED);
    }

    /**
     * Slices ranked by count, the tail folded into "Others", and the missing
     * value last so it never takes a ranked slot.
     *
     * @param  list<array{label: ?string, count: int, id?: ?int, min?: int|float|null, max?: int|float|null, value?: ?int}>  $rows
     * @return array{kind: string, metric: string, total: int, slices: list<array<string, mixed>>, values: list<array<string, mixed>>}
     */
    private function chart(string $metric, array $rows, string $missingLabel = self::NOT_SPECIFIED): array
    {
        $values = [];
        $missing = 0;

        foreach ($rows as $row) {
            if ($row['count'] <= 0) {
                continue;
            }

            if ($row['label'] === null || trim((string) $row['label']) === '') {
                $missing += $row['count'];

                continue;
            }

            $slice = ['label' => $row['label'], 'count' => $row['count'], 'kind' => 'value'];

            if (array_key_exists('id', $row) && $row['id'] !== null) {
                $slice['id'] = $row['id'];
            }

            if (array_key_exists('min', $row)) {
                $slice['min'] = $row['min'];
            }

            if (array_key_exists('max', $row)) {
                $slice['max'] = $row['max'];
            }

            if (array_key_exists('value', $row)) {
                $slice['value'] = $row['value'];
            }

            if (array_key_exists('choice', $row) && $row['choice'] !== null) {
                $slice['choice'] = $row['choice'];
            }

            $values[] = $slice;
        }

        usort($values, fn (array $a, array $b): int => [$b['count'], $a['label']] <=> [$a['count'], $b['label']]);

        $slices = $values;

        if (count($slices) > self::TOP_SLICES + 1) {
            $rest = array_slice($slices, self::TOP_SLICES);
            $slices = array_slice($slices, 0, self::TOP_SLICES);
            $slices[] = [
                'label' => 'Others',
                'count' => array_sum(array_column($rest, 'count')),
                'kind' => 'others',
                'grouped' => count($rest),
                'ids' => array_values(array_filter(array_column($rest, 'id'))),
            ];
        }

        if ($missing > 0) {
            $slices[] = ['label' => $missingLabel, 'count' => $missing, 'kind' => 'missing'];
        }

        return [
            'kind' => 'pie',
            'metric' => $metric,
            'total' => array_sum(array_column($slices, 'count')),
            'slices' => $slices,
            'values' => $values,
        ];
    }

    /**
     * @param  list<array{key: string, count: int, items: list<array{id: int, name: string}>}>  $groups
     * @return array{kind: string, metric: string, total: int, groups: list<array<string, mixed>>, slices: list<array<string, mixed>>}
     */
    private function booleanChart(string $metric, array $groups, string $yesLabel, string $noLabel): array
    {
        $labelled = [];

        foreach ($groups as $group) {
            if (($group['key'] ?? '') === 'missing') {
                continue;
            }

            $label = ($group['key'] ?? '') === 'yes' ? $yesLabel : $noLabel;
            $labelled[] = [
                'key' => $group['key'],
                'label' => $label,
                'count' => $group['count'],
                'items' => $group['items'] ?? [],
            ];
        }

        return [
            'kind' => 'boolean',
            'metric' => $metric,
            'total' => array_sum(array_column($labelled, 'count')),
            'groups' => $labelled,
            'slices' => [],
        ];
    }

    /**
     * @param  list<array{value: ?int, count: int}>  $rows
     * @return list<array{label: ?string, count: int, value: ?int}>
     */
    private function storyRows(array $rows): array
    {
        return array_map(fn (array $row): array => [
            'label' => $row['value'] === null ? null : $row['value'].' '.($row['value'] === 1 ? 'Story' : 'Stories'),
            'count' => $row['count'],
            'value' => $row['value'],
        ], $rows);
    }

    /**
     * @param  list<array{value: ?int, count: int}>  $rows
     * @return list<array{label: ?string, count: int, value: ?int}>
     */
    private function basementRows(array $rows): array
    {
        return array_map(fn (array $row): array => [
            'label' => match (true) {
                $row['value'] === null => null,
                $row['value'] === 0 => 'No Basement',
                $row['value'] === 1 => '1 Basement Level',
                default => $row['value'].' Basement Levels',
            },
            'count' => $row['count'],
            'value' => $row['value'],
        ], $rows);
    }

    /**
     * A household that answered neither ownership question is unanswered; one
     * missing side still shows the side that was answered.
     *
     * @param  list<array{unit: ?string, lot: ?string, count: int}>  $rows
     * @return list<array{label: ?string, count: int}>
     */
    private function ownershipRows(array $rows): array
    {
        $merged = [];

        foreach ($rows as $row) {
            $label = $row['unit'] === null && $row['lot'] === null
                ? null
                : 'Unit: '.($row['unit'] ?? self::NOT_ANSWERED).' / Lot: '.($row['lot'] ?? self::NOT_ANSWERED);
            $key = $label ?? '';
            $merged[$key] = [
                'label' => $label,
                'count' => ($merged[$key]['count'] ?? 0) + $row['count'],
            ];
        }

        return array_values($merged);
    }
}
