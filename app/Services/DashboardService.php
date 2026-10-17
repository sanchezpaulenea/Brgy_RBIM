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

    public function __construct(
        protected DashboardRepositoryInterface $dashboardRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function statistics(): array
    {
        $repo = $this->dashboardRepository;

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
                'fuel_lighting' => $this->questionChart('fuel_type_for_lighting_id', 'fuel_type', 'fuel_type_id', 'fuel_type'),
                'fuel_cooking' => $this->questionChart('fuel_type_for_cooking_id', 'fuel_type', 'fuel_type_id', 'fuel_type'),
                'water_source' => $this->questionChart('main_source_drinking_water_id', 'water_source', 'water_source_id', 'water_source'),
                'kitchen_garbage' => $this->questionChart('kitchen_garbage_disposal_id', 'kitchen_garbage_disposal', 'kitchen_garbage_disposal_id', 'kitchen_garbage_disposal'),
                'toilet_facility' => $this->questionChart('toilet_facility_type_id', 'toilet_facility_type', 'toilet_facility_type_id', 'toilet_facility_type'),
                'building_type' => $this->questionChart('type_of_building_house_id', 'building_house_type', 'building_house_type_id', 'building_house_type'),
                'construction_material' => $this->questionChart('construction_material_outer_wall_id', 'construction_material_outer_wall', 'construction_material_outer_wall_id', 'construction_material_outer_wall'),

                'common_disease' => $this->chart('Total Diseases Recorded', $repo->questionJunctionCounts('common_disease', 'common_disease_id', 'common_disease_cause_death_in_brgy', 'common_disease_id', 'common_disease')),
                'primary_need' => $this->chart('Total Primary Needs Recorded', $repo->questionJunctionCounts('primary_need', 'primary_need_id', 'primary_need_of_brgy', 'primary_need_id', 'primary_need')),

                'relationship_to_hh' => $this->chart(self::RESIDENTS, $repo->residentsByLookup('relationship_to_hh_id', 'relationship_to_hh', 'relationship_to_hh_id', 'relationship_to_hh')),
                'nationality' => $this->chart(self::RESIDENTS, $repo->residentsByLookup('nationality_id', 'nationality', 'nationality_id', 'nationality')),
                'marital_status' => $this->chart(self::RESIDENTS, $repo->residentsByLookup('marital_status_id', 'marital_status', 'marital_status_id', 'marital_status')),
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
     * @param  list<array{label: ?string, count: int}>  $rows
     * @return array{metric: string, total: int, slices: list<array{label: string, count: int, kind: string, grouped?: int}>}
     */
    private function chart(string $metric, array $rows, string $missingLabel = self::NOT_SPECIFIED): array
    {
        $values = [];
        $missing = 0;

        foreach ($rows as $row) {
            if ($row['count'] <= 0) {
                continue;
            }

            if ($row['label'] === null || trim($row['label']) === '') {
                $missing += $row['count'];

                continue;
            }

            $values[] = ['label' => $row['label'], 'count' => $row['count'], 'kind' => 'value'];
        }

        usort($values, fn (array $a, array $b): int => [$b['count'], $a['label']] <=> [$a['count'], $b['label']]);

        if (count($values) > self::TOP_SLICES + 1) {
            $rest = array_slice($values, self::TOP_SLICES);
            $values = array_slice($values, 0, self::TOP_SLICES);
            $values[] = [
                'label' => 'Others',
                'count' => array_sum(array_column($rest, 'count')),
                'kind' => 'others',
                'grouped' => count($rest),
            ];
        }

        if ($missing > 0) {
            $values[] = ['label' => $missingLabel, 'count' => $missing, 'kind' => 'missing'];
        }

        return [
            'metric' => $metric,
            'total' => array_sum(array_column($values, 'count')),
            'slices' => $values,
        ];
    }

    /**
     * @param  list<array{value: ?int, count: int}>  $rows
     * @return list<array{label: ?string, count: int}>
     */
    private function storyRows(array $rows): array
    {
        return array_map(fn (array $row): array => [
            'label' => $row['value'] === null ? null : $row['value'].' '.($row['value'] === 1 ? 'Story' : 'Stories'),
            'count' => $row['count'],
        ], $rows);
    }

    /**
     * @param  list<array{value: ?int, count: int}>  $rows
     * @return list<array{label: ?string, count: int}>
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
