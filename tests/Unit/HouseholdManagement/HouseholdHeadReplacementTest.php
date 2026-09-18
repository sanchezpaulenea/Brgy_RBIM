<?php

namespace Tests\Unit\HouseholdManagement;

use App\Models\HouseholdManagement\Household;
use App\Models\HouseholdManagement\HouseholdStatus;
use App\Models\ResidentManagement\Demographic\RelationshipToHouseholdHead;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Demographic\ResidentStatus;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Demographic\ResidentRepositoryInterface;
use App\Services\HouseholdManagement\HouseholdQuestionsService;
use App\Services\HouseholdManagement\HouseholdServices;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class HouseholdHeadReplacementTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_reassign_head_updates_the_household_and_new_head_relationship(): void
    {
        $household = new Household([
            'head_resident_id' => 1,
            'household_status_id' => HouseholdStatus::ACTIVE,
        ]);
        $household->household_id = 10;

        $formerHead = new Resident([
            'household_id' => 10,
            'last_name' => 'Santos',
            'first_name' => 'Ana',
        ]);
        $formerHead->resident_id = 1;

        $newHead = new Resident([
            'household_id' => 10,
            'resident_status_id' => ResidentStatus::ACTIVE,
            'date_of_birth' => now()->subYears(30)->toDateString(),
            'last_name' => 'Santos',
            'first_name' => 'Ben',
        ]);
        $newHead->resident_id = 2;

        $householdRepository = Mockery::mock(HouseholdRepositoryInterface::class);
        $householdRepository->shouldReceive('lockById')->once()->with(10)->andReturn($household);
        $householdRepository->shouldReceive('updateHeadResident')->once()->with($household, 2)->andReturn($household);

        $residentRepository = Mockery::mock(ResidentRepositoryInterface::class);
        $residentRepository->shouldReceive('findById')->twice()->with(2)->andReturn($newHead);
        $residentRepository->shouldReceive('update')->once()->with($newHead, [
            'relationship_to_hh_id' => RelationshipToHouseholdHead::HEAD,
        ])->andReturn($newHead);

        $auditLogRepository = Mockery::mock(AuditLogRepositoryInterface::class);
        $auditLogRepository->shouldReceive('log')->once();

        $service = new HouseholdServices(
            $householdRepository,
            $residentRepository,
            $auditLogRepository,
            Mockery::mock(HouseholdQuestionsService::class),
        );

        $user = new User;
        $user->user_id = 5;

        $service->reassignHead($user, $formerHead, 2, RelationshipToHouseholdHead::HEAD + 1);

        $this->assertTrue(true);
    }

    public function test_reassign_head_rejects_a_member_from_another_household(): void
    {
        $household = new Household([
            'head_resident_id' => 1,
            'household_status_id' => HouseholdStatus::ACTIVE,
        ]);
        $household->household_id = 10;

        $formerHead = new Resident(['household_id' => 10]);
        $formerHead->resident_id = 1;

        $outsider = new Resident([
            'household_id' => 99,
            'resident_status_id' => ResidentStatus::ACTIVE,
            'date_of_birth' => now()->subYears(30)->toDateString(),
        ]);
        $outsider->resident_id = 8;

        $householdRepository = Mockery::mock(HouseholdRepositoryInterface::class);
        $householdRepository->shouldReceive('lockById')->once()->with(10)->andReturn($household);

        $residentRepository = Mockery::mock(ResidentRepositoryInterface::class);
        $residentRepository->shouldReceive('findById')->once()->with(8)->andReturn($outsider);

        $service = new HouseholdServices(
            $householdRepository,
            $residentRepository,
            Mockery::mock(AuditLogRepositoryInterface::class),
            Mockery::mock(HouseholdQuestionsService::class),
        );

        $user = new User;
        $user->user_id = 5;

        $this->expectException(ValidationException::class);

        $service->reassignHead($user, $formerHead, 8, 2);
    }
}
