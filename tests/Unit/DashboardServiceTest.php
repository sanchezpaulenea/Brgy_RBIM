<?php

namespace Tests\Unit;

use App\Repositories\Interfaces\DashboardRepositoryInterface;
use App\Services\DashboardService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    /** @var DashboardRepositoryInterface&MockInterface */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(DashboardRepositoryInterface::class);
        $this->repository->shouldReceive('summaryCounts')->andReturn(['households' => 0, 'residents' => 0, 'clans' => 0, 'streets' => 0]);
        $this->repository->shouldReceive('householdsByLookup')->andReturn([])->byDefault();
        $this->repository->shouldReceive('householdsByQuestionLookup')->andReturn([])->byDefault();
        $this->repository->shouldReceive('householdsByOwnership')->andReturn([])->byDefault();
        $this->repository->shouldReceive('householdsByNumber')->andReturn([])->byDefault();
        $this->repository->shouldReceive('questionJunctionCounts')->andReturn([])->byDefault();
        $this->repository->shouldReceive('residentsByLookup')->andReturn([])->byDefault();
        $this->repository->shouldReceive('householdsByQuestionBoolean')->andReturn([])->byDefault();
        $this->repository->shouldReceive('householdsByPresence')->andReturn([])->byDefault();
        $this->repository->shouldReceive('petsByLookup')->andReturn([])->byDefault();
        $this->repository->shouldReceive('residentsByChildLookup')->andReturn([])->byDefault();
        $this->repository->shouldReceive('residentsByChildBoolean')->andReturn([])->byDefault();
        $this->repository->shouldReceive('residentsByColumnPresence')->andReturn([])->byDefault();
        $this->repository->shouldReceive('residentsByChildColumn')->andReturn([])->byDefault();
        $this->repository->shouldReceive('residentsByAgeBuckets')->andReturn([])->byDefault();
        $this->repository->shouldReceive('residentsByChildNumberBuckets')->andReturn([])->byDefault();
        $this->repository->shouldReceive('petsByBoolean')->andReturn([])->byDefault();
        $this->repository->shouldReceive('petsByRabies')->andReturn([])->byDefault();
        $this->repository->shouldReceive('residentsBySentinel')->andReturn([])->byDefault();
        $this->repository->shouldReceive('residentsByCtcStatus')->andReturn([])->byDefault();
        $this->repository->shouldReceive('residentsByTriState')->andReturn([])->byDefault();
        $this->repository->shouldReceive('residentsByVoterStatus')->andReturn([])->byDefault();
        $this->repository->shouldReceive('residentsByChildDistinct')->andReturn([])->byDefault();
        $this->repository->shouldReceive('residentsByDisability')->andReturn([])->byDefault();
    }

    public function test_many_values_keep_top_five_and_group_the_rest_as_others(): void
    {
        $this->repository->shouldReceive('householdsByLookup')
            ->with('street_id', 'street', 'street_id', 'street_name')
            ->andReturn([
                ['label' => 'A', 'count' => 1],
                ['label' => 'B', 'count' => 9],
                ['label' => 'C', 'count' => 7],
                ['label' => 'D', 'count' => 5],
                ['label' => 'E', 'count' => 3],
                ['label' => 'F', 'count' => 2],
                ['label' => 'G', 'count' => 2],
                ['label' => null, 'count' => 4],
            ]);

        $chart = $this->statistics()['charts']['street'];

        $this->assertSame(['B', 'C', 'D', 'E', 'F', 'Others', 'Not Specified'], array_column($chart['slices'], 'label'));
        $this->assertSame(3, $chart['slices'][5]['count']);
        $this->assertSame(2, $chart['slices'][5]['grouped']);
        $this->assertSame(33, $chart['total']);
        $this->assertSame('Total Household Records', $chart['metric']);
    }

    public function test_one_value_past_the_limit_is_shown_by_name(): void
    {
        $rows = array_map(fn (string $label): array => ['label' => $label, 'count' => 1], ['A', 'B', 'C', 'D', 'E', 'F']);
        $this->repository->shouldReceive('residentsByLookup')
            ->with('clan_id', 'clan', 'clan_id', 'clan_name')
            ->andReturn($rows);

        $slices = $this->statistics()['charts']['clan_residents']['slices'];

        $this->assertSame(['A', 'B', 'C', 'D', 'E', 'F'], array_column($slices, 'label'));
    }

    public function test_unanswered_questions_are_labelled_not_answered(): void
    {
        $this->repository->shouldReceive('householdsByQuestionLookup')
            ->with('main_source_drinking_water_id', 'water_source', 'water_source_id', 'water_source')
            ->andReturn([['label' => null, 'count' => 3], ['label' => 'Shared faucet', 'count' => 2]]);

        $slices = $this->statistics()['charts']['water_source']['slices'];

        $this->assertSame(['Shared faucet', 'Not Answered'], array_column($slices, 'label'));
        $this->assertSame('missing', $slices[1]['kind']);
    }

    public function test_ownership_combines_unit_and_lot_into_one_chart(): void
    {
        $this->repository->shouldReceive('householdsByOwnership')->andReturn([
            ['unit' => 'Owned', 'lot' => 'Rented', 'count' => 4],
            ['unit' => 'Owned', 'lot' => null, 'count' => 1],
            ['unit' => null, 'lot' => null, 'count' => 2],
        ]);

        $chart = $this->statistics()['charts']['ownership'];

        $this->assertSame(
            ['Unit: Owned / Lot: Rented', 'Unit: Owned / Lot: Not Answered', 'Not Answered'],
            array_column($chart['slices'], 'label'),
        );
        $this->assertSame(7, $chart['total']);
    }

    public function test_house_story_and_basement_values_get_readable_labels(): void
    {
        $this->repository->shouldReceive('householdsByNumber')
            ->with('number_of_house_story')
            ->andReturn([['value' => 1, 'count' => 5], ['value' => 2, 'count' => 3]]);
        $this->repository->shouldReceive('householdsByNumber')
            ->with('number_of_basement_level', 0)
            ->andReturn([['value' => 0, 'count' => 6], ['value' => 1, 'count' => 1], ['value' => 3, 'count' => 1]]);

        $charts = $this->statistics()['charts'];

        $this->assertSame(['1 Story', '2 Stories'], array_column($charts['house_story']['slices'], 'label'));
        $this->assertSame(['No Basement', '1 Basement Level', '3 Basement Levels'], array_column($charts['basement_level']['slices'], 'label'));
    }

    public function test_disease_and_need_charts_use_their_own_metric(): void
    {
        $charts = $this->statistics()['charts'];

        $this->assertSame('Total Diseases Recorded', $charts['common_disease']['metric']);
        $this->assertSame('Total Primary Needs Recorded', $charts['primary_need']['metric']);
        $this->assertSame('Total Resident Records', $charts['marital_status']['metric']);
    }

    public function test_boolean_charts_list_yes_and_no_without_a_pie(): void
    {
        $this->repository->shouldReceive('householdsByQuestionBoolean')
            ->with('perform_garbage_seggragation')
            ->andReturn([
                ['key' => 'yes', 'count' => 4, 'items' => [['id' => 1, 'name' => 'Dela Cruz, Ana']]],
                ['key' => 'no', 'count' => 2, 'items' => [['id' => 2, 'name' => 'Santos, Ben']]],
            ]);

        $chart = $this->statistics()['charts']['segregate_garbage'];

        $this->assertSame('boolean', $chart['kind']);
        $this->assertSame(['Segregates', 'Does Not Segregate'], array_column($chart['groups'], 'label'));
        $this->assertSame(6, $chart['total']);
    }

    public function test_added_modules_are_present_on_the_dashboard(): void
    {
        $charts = $this->statistics()['charts'];

        foreach ([
            'pets', 'pets_sex', 'pets_rabies', 'pets_spay',
            'education', 'education_enrollment',
            'economic', 'economic_source', 'economic_work_status',
            'health', 'health_facility', 'health_reason', 'health_disability',
            'infant_health', 'infant_attendant', 'infant_immunization',
            'women_health', 'women_fp', 'women_fp_source', 'women_fp_intention',
            'ctc', 'skills_training', 'skills', 'age', 'age_disability',
            'ownership_unit', 'ownership_lot',
        ] as $key) {
            $this->assertArrayHasKey($key, $charts);
        }

        $this->assertSame('pie', $charts['ctc']['kind']);
    }

    /**
     * @return array<string, mixed>
     */
    private function statistics(): array
    {
        return (new DashboardService($this->repository))->statistics();
    }
}
