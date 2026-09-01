<?php

namespace Tests\Unit\HouseholdManagement;

use App\Models\HouseholdManagement\CensusStatus;
use App\Models\HouseholdManagement\HouseholdAssessment;
use App\Repositories\Interfaces\BarangayPersonnel\BarangayPersonnelRepositoryInterface;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdAssessmentRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Services\HouseholdManagement\HouseholdAssessmentServices;
use Mockery;
use Tests\TestCase;

class HouseholdAssessmentServicesTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_format_assessment_includes_previous_assessment_summary(): void
    {
        $previous = new HouseholdAssessment([
            'household_id' => 1,
            'census_status_id' => CensusStatus::CALLBACK,
        ]);
        $previous->assessment_id = 2;
        $previous->setRelation('censusStatus', new CensusStatus([
            'status_code' => 'CB',
            'status_name' => 'Callback',
        ]));
        $previous->setRelation('encoder', null);
        $previous->setRelation('interviewer', null);
        $previous->setRelation('supervisor', null);
        $previous->setRelation('previousAssessment', null);

        $current = new HouseholdAssessment([
            'household_id' => 1,
            'census_status_id' => CensusStatus::COMPLETED,
            'previous_assessment_id' => 2,
        ]);
        $current->assessment_id = 3;
        $current->setRelation('censusStatus', new CensusStatus([
            'status_code' => 'C',
            'status_name' => 'Completed',
        ]));
        $current->setRelation('encoder', null);
        $current->setRelation('interviewer', null);
        $current->setRelation('supervisor', null);
        $current->setRelation('previousAssessment', $previous);

        $service = new HouseholdAssessmentServices(
            Mockery::mock(HouseholdAssessmentRepositoryInterface::class),
            Mockery::mock(BarangayPersonnelRepositoryInterface::class),
            Mockery::mock(AuditLogRepositoryInterface::class),
        );

        $payload = $service->formatAssessment($current);

        $this->assertSame(3, $payload['assessment_id']);
        $this->assertSame(2, $payload['previous_assessment_id']);
        $this->assertSame('C (Completed)', $payload['census_status_label']);
        $this->assertSame(2, $payload['previous_assessment']['assessment_id']);
        $this->assertSame('CB (Callback)', $payload['previous_assessment']['census_status_label']);
        $this->assertArrayNotHasKey('previous_assessment', $payload['previous_assessment']);
    }
}
