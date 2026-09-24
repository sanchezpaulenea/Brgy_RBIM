<?php

namespace Tests\Unit\HouseholdManagement;

use App\Models\HouseholdManagement\PetCensus;
use App\Models\UserManagement\User;
use App\Policies\HouseholdManagement\PetCensusPolicy;
use Mockery;
use Tests\TestCase;

class PetCensusPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_encoder_can_create_pet_census_when_permitted(): void
    {
        $this->assertTrue((new PetCensusPolicy)->create($this->user([
            'isAdmin' => false,
            'permissions' => ['household.create'],
        ])));
    }

    public function test_updater_can_update_pet_census(): void
    {
        $policy = new PetCensusPolicy;
        $user = $this->user([
            'isAdmin' => false,
            'permissions' => ['household.update'],
        ]);

        $this->assertTrue($policy->update($user, new PetCensus));
        $this->assertFalse($policy->create($this->user([
            'isAdmin' => false,
            'permissions' => ['household.view'],
        ])));
    }

    /**
     * @param  array{isAdmin: bool, permissions: list<string>}  $attributes
     */
    private function user(array $attributes): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('isAdmin')->zeroOrMoreTimes()->andReturn($attributes['isAdmin']);
        $user->shouldReceive('hasPermission')->zeroOrMoreTimes()->andReturnUsing(
            fn (string $permission) => in_array($permission, $attributes['permissions'], true)
        );

        return $user;
    }
}
