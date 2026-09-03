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

    public function test_admin_can_view_settings_but_cannot_update_them(): void
    {
        $policy = new SystemSettingPolicy;
        $admin = $this->user(true, false, ['setting.view', 'setting.update']);
        $setting = new Setting;

        $this->assertTrue($policy->viewAny($admin));
        $this->assertFalse($policy->update($admin, $setting));
    }

    public function test_super_admin_can_view_and_update_settings_when_permitted(): void
    {
        $policy = new SystemSettingPolicy;
        $superAdmin = $this->user(true, true, ['setting.view', 'setting.update']);
        $setting = new Setting;

        $this->assertTrue($policy->viewAny($superAdmin));
        $this->assertTrue($policy->update($superAdmin, $setting));
    }

    public function test_encoder_cannot_view_or_update_settings(): void
    {
        $policy = new SystemSettingPolicy;
        $encoder = $this->user(false, false, ['setting.view', 'setting.update']);
        $setting = new Setting;

        $this->assertFalse($policy->viewAny($encoder));
        $this->assertFalse($policy->update($encoder, $setting));
    }

    /**
     * @param  list<string>  $permissions
     */
    private function user(bool $isSystemAdministrator, bool $isSuperAdmin, array $permissions): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('isSystemAdministrator')->zeroOrMoreTimes()->andReturn($isSystemAdministrator);
        $user->shouldReceive('isSuperAdmin')->zeroOrMoreTimes()->andReturn($isSuperAdmin);
        $user->shouldReceive('hasPermission')->zeroOrMoreTimes()->andReturnUsing(
            fn (string $permission) => in_array($permission, $permissions, true)
        );

        return $user;
    }
}
