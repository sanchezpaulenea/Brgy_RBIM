<?php

namespace Tests\Unit\Authentication;

use App\Http\Requests\Authentication\UpdateProfileAvatarRequest;
use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\UserManagement\User;
use App\Services\Authentication\AuthenticationService;
use Tests\TestCase;

class SessionUserPayloadTest extends TestCase
{
    public function test_session_user_includes_name_position_and_avatar_fields(): void
    {
        $user = new User([
            'username' => 'cgalpao',
            'must_change_password' => false,
        ]);
        $user->user_id = 8;
        $user->avatar_preset = User::AVATAR_FEMALE;
        $user->avatar_path = null;

        $personnel = new BarangayPersonnel([
            'personnel_first_name' => 'Cristine',
            'personnel_last_name' => 'Galpao',
        ]);
        $personnel->setRelation('position', new PersonnelPosition([
            'position_name' => 'Barangay Secretary',
        ]));
        $user->setRelation('personnel', $personnel);

        $service = $this->app->make(AuthenticationService::class);
        $payload = $service->sessionUser($user);

        $this->assertSame(8, $payload['user_id']);
        $this->assertSame('cgalpao', $payload['username']);
        $this->assertSame('Cristine Galpao', $payload['full_name']);
        $this->assertSame('Cristine', $payload['first_name']);
        $this->assertSame('Galpao', $payload['last_name']);
        $this->assertSame('Barangay Secretary', $payload['position_name']);
        $this->assertSame(User::AVATAR_FEMALE, $payload['avatar_preset']);
        $this->assertNull($payload['avatar_url']);
        $this->assertFalse($payload['must_change_password']);
    }

    public function test_avatar_request_requires_a_photo_or_preset(): void
    {
        $request = new UpdateProfileAvatarRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('photo', $rules);
        $this->assertArrayHasKey('avatar_preset', $rules);
    }
}
