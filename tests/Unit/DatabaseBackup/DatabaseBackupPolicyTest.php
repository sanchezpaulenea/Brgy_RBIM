<?php

namespace Tests\Unit\DatabaseBackup;

use App\Models\UserManagement\User;
use App\Policies\DatabaseBackup\DatabaseBackupPolicy;
use Mockery;
use Tests\TestCase;

class DatabaseBackupPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_admin_can_export_but_cannot_restore(): void
    {
        $policy = new DatabaseBackupPolicy;
        $admin = $this->user(true, false, ['backup.export', 'backup.restore']);

        $this->assertTrue($policy->export($admin));
        $this->assertFalse($policy->restore($admin));
    }

    public function test_super_admin_can_export_and_restore_when_permitted(): void
    {
        $policy = new DatabaseBackupPolicy;
        $superAdmin = $this->user(true, true, ['backup.export', 'backup.restore']);

        $this->assertTrue($policy->export($superAdmin));
        $this->assertTrue($policy->restore($superAdmin));
    }

    public function test_super_admin_cannot_restore_without_permission(): void
    {
        $policy = new DatabaseBackupPolicy;
        $superAdmin = $this->user(true, true, ['backup.export']);

        $this->assertFalse($policy->restore($superAdmin));
    }

    public function test_encoder_cannot_export_or_restore(): void
    {
        $policy = new DatabaseBackupPolicy;
        $encoder = $this->user(false, false, ['backup.export', 'backup.restore']);

        $this->assertFalse($policy->export($encoder));
        $this->assertFalse($policy->restore($encoder));
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
            fn (string $permission): bool => in_array($permission, $permissions, true)
        );

        return $user;
    }
}
