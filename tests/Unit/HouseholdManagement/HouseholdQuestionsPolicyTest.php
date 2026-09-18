<?php

namespace Tests\Unit\HouseholdManagement;

use App\Models\HouseholdManagement\HouseholdQuestions;
use App\Models\UserManagement\User;
use App\Policies\HouseholdManagement\HouseholdQuestionsPolicy;
use Mockery;
use Tests\TestCase;

class HouseholdQuestionsPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_encoder_can_create_household_questions_when_permitted(): void
    {
        $this->assertTrue((new HouseholdQuestionsPolicy)->create($this->user([
            'isAdmin' => false,
            'permissions' => ['household.create'],
        ])));
    }

    public function test_admin_can_update_household_questions(): void
    {
        $policy = new HouseholdQuestionsPolicy;
        $admin = $this->user([
            'isAdmin' => true,
            'permissions' => [],
        ]);
        $questions = new HouseholdQuestions;

        $this->assertTrue($policy->view($admin, $questions));
        $this->assertTrue($policy->update($admin, $questions));
        $this->assertTrue($policy->create($admin));
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
