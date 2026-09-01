<?php

namespace Tests\Unit\HouseholdManagement;

use App\Models\HouseholdManagement\Household;
use App\Models\UserManagement\User;
use App\Policies\HouseholdManagement\HouseholdPolicy;
use Mockery;
use Tests\TestCase;

class HouseholdPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_admin_can_create_a_household_when_permitted(): void
    {
        $this->assertTrue((new HouseholdPolicy)->create($this->user([
            'isAdmin' => true,
            'isEncoder' => false,
            'permissions' => ['household.create'],
        ])));
    }

    public function test_encoder_can_create_a_household_when_permitted(): void
    {
        $this->assertTrue((new HouseholdPolicy)->create($this->user([
            'isAdmin' => false,
            'isEncoder' => true,
            'permissions' => ['household.create'],
        ])));
    }

    public function test_super_admin_cannot_create_a_household(): void
    {
        $this->assertFalse((new HouseholdPolicy)->create($this->user([
            'isAdmin' => false,
            'isEncoder' => false,
            'permissions' => ['household.create'],
        ])));
    }

    public function test_admin_can_view_and_update_households(): void
    {
        $policy = new HouseholdPolicy;
        $admin = $this->user([
            'isAdmin' => true,
            'isEncoder' => false,
            'permissions' => [],
        ]);
        $household = new Household;

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->view($admin, $household));
        $this->assertTrue($policy->update($admin, $household));
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
