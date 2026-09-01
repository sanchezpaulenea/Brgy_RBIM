<?php

namespace Tests\Unit\HouseholdManagement;

use App\Models\HouseholdManagement\HouseholdAssessment;
use App\Models\UserManagement\User;
use App\Policies\HouseholdManagement\HouseholdAssessmentPolicy;
use Mockery;
use Tests\TestCase;

class HouseholdAssessmentPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_admin_can_create_an_assessment_when_permitted(): void
    {
        $this->assertTrue((new HouseholdAssessmentPolicy)->create($this->user([
            'isAdmin' => true,
            'isEncoder' => false,
            'permissions' => ['householdassessment.create'],
        ])));
    }

    public function test_encoder_can_create_an_assessment_when_permitted(): void
    {
        $this->assertTrue((new HouseholdAssessmentPolicy)->create($this->user([
            'isAdmin' => false,
            'isEncoder' => true,
            'permissions' => ['householdassessment.create'],
        ])));
    }

    public function test_super_admin_cannot_create_an_assessment(): void
    {
        $this->assertFalse((new HouseholdAssessmentPolicy)->create($this->user([
            'isAdmin' => false,
            'isEncoder' => false,
            'permissions' => ['householdassessment.create'],
        ])));
    }

    public function test_admin_can_view_assessments(): void
    {
        $policy = new HouseholdAssessmentPolicy;
        $admin = $this->user([
            'isAdmin' => true,
            'isEncoder' => false,
            'permissions' => [],
        ]);
        $assessment = new HouseholdAssessment;

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->view($admin, $assessment));
    }

    /**
     * @param  array{isAdmin: bool, isEncoder: bool, permissions: list<string>}  $attributes
     */
    private function user(array $attributes): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('isAdmin')->zeroOrMoreTimes()->andReturn($attributes['isAdmin']);
        $user->shouldReceive('isEncoder')->zeroOrMoreTimes()->andReturn($attributes['isEncoder']);
        $user->shouldReceive('hasPermission')->zeroOrMoreTimes()->andReturnUsing(
            fn (string $permission) => in_array($permission, $attributes['permissions'], true)
        );

        return $user;
    }
}
