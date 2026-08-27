<?php

namespace Tests\Unit\UserManagement;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\UserManagement\User;
use Tests\TestCase;

class UserProfileDisplayTest extends TestCase
{
    public function test_profile_display_name_uses_given_then_family_name(): void
    {
        $user = $this->userWithPersonnel('Cristine', 'Galpao', 'Barangay Secretary');

        $this->assertSame('Cristine Galpao', $user->profileDisplayName());
        $this->assertSame('Cristine', $user->profileGivenName());
        $this->assertSame('Galpao', $user->profileFamilyName());
        $this->assertSame('Barangay Secretary', $user->profilePositionName());
    }

    public function test_profile_display_name_falls_back_to_username(): void
    {
        $user = new User(['username' => 'guest.account']);
        $user->setRelation('personnel', null);

        $this->assertSame('Guest.account', $user->profileDisplayName());
        $this->assertSame('', $user->profileGivenName());
        $this->assertSame('', $user->profileFamilyName());
        $this->assertNull($user->profilePositionName());
    }

    public function test_avatar_url_is_null_until_a_photo_is_stored(): void
    {
        $user = new User(['username' => 'encoder']);
        $user->avatar_path = null;

        $this->assertNull($user->avatarUrl());

        $user->avatar_path = 'avatars/4.jpg';

        $this->assertSame('/api/v1/auth/profile/avatar?v='.substr(md5('avatars/4.jpg'), 0, 8), $user->avatarUrl());
    }

    private function userWithPersonnel(string $firstName, string $lastName, string $positionName): User
    {
        $user = new User(['username' => 'cgalpao']);
        $personnel = new BarangayPersonnel([
            'personnel_first_name' => $firstName,
            'personnel_last_name' => $lastName,
        ]);
        $personnel->setRelation('position', new PersonnelPosition([
            'position_name' => $positionName,
        ]));
        $user->setRelation('personnel', $personnel);

        return $user;
    }
}
