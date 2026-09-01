<?php

namespace Tests\Unit\ResidentManagement;

use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\UserManagement\User;
use App\Policies\ResidentManagement\Demographic\ResidentPolicy;
use Mockery;
use Tests\TestCase;

class ResidentPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_admin_can_create_a_resident_when_permitted(): void
    {
        $this->assertTrue((new ResidentPolicy)->create($this->user([
            'isAdmin' => true,
            'isEncoder' => false,
            'permissions' => ['resident.create'],
        ])));
    }

    public function test_encoder_can_create_a_resident_when_permitted(): void
    {
        $this->assertTrue((new ResidentPolicy)->create($this->user([
            'isAdmin' => false,
            'isEncoder' => true,
            'permissions' => ['resident.create'],
        ])));
    }

    public function test_super_admin_cannot_create_a_resident(): void
    {
        $this->assertFalse((new ResidentPolicy)->create($this->user([
            'isAdmin' => false,
            'isEncoder' => false,
            'permissions' => ['resident.create'],
        ])));
    }

    public function test_admin_can_view_and_update_residents(): void
    {
        $policy = new ResidentPolicy;
        $admin = $this->user([
            'isAdmin' => true,
            'isEncoder' => false,
            'permissions' => [],
        ]);
        $resident = new Resident;

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->view($admin, $resident));
        $this->assertTrue($policy->update($admin, $resident));
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
