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

    public function test_user_with_pet_view_can_list_and_view(): void
    {
        $policy = new PetCensusPolicy;
        $user = $this->user(['pet.view']);

        $this->assertTrue($policy->viewAny($user));
        $this->assertTrue($policy->view($user, new PetCensus));
    }

    public function test_household_permissions_do_not_grant_pet_access(): void
    {
        $policy = new PetCensusPolicy;
        $user = $this->user(['household.view', 'household.create', 'household.update']);

        $this->assertFalse($policy->viewAny($user));
        $this->assertFalse($policy->view($user, new PetCensus));
        $this->assertFalse($policy->create($user));
        $this->assertFalse($policy->update($user, new PetCensus));
    }

    public function test_create_and_update_require_their_own_permissions(): void
    {
        $policy = new PetCensusPolicy;

        $this->assertTrue($policy->create($this->user(['pet.create'])));
        $this->assertTrue($policy->update($this->user(['pet.update']), new PetCensus));
        $this->assertFalse($policy->create($this->user(['pet.view'])));
        $this->assertFalse($policy->update($this->user(['pet.view']), new PetCensus));
    }

    /**
     * @param  list<string>  $permissions
     */
    private function user(array $permissions): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasPermission')->zeroOrMoreTimes()->andReturnUsing(
            fn (string $permission) => in_array($permission, $permissions, true)
        );

        return $user;
    }
}
