<?php

namespace Tests\Unit\SystemSetting;

use App\Models\Setting\Setting;
use App\Models\UserManagement\User;
use App\Policies\SystemSetting\SystemSettingPolicy;
use Mockery;
use Tests\TestCase;

class SystemSettingPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_admin_can_view_and_update_settings_when_permitted(): void
    {
        $policy = new SystemSettingPolicy;
        $admin = $this->user(true, ['setting.view', 'setting.update']);
        $setting = new Setting;

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->update($admin, $setting));
    }

    public function test_encoder_cannot_view_or_update_settings(): void
    {
        $policy = new SystemSettingPolicy;
        $encoder = $this->user(false, ['setting.view', 'setting.update']);
        $setting = new Setting;

        $this->assertFalse($policy->viewAny($encoder));
        $this->assertFalse($policy->update($encoder, $setting));
    }

    /**
     * @param  list<string>  $permissions
     */
    private function user(bool $isSystemAdministrator, array $permissions): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('isSystemAdministrator')->zeroOrMoreTimes()->andReturn($isSystemAdministrator);
        $user->shouldReceive('hasPermission')->zeroOrMoreTimes()->andReturnUsing(
            fn (string $permission) => in_array($permission, $permissions, true)
        );

        return $user;
    }
}
