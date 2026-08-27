<?php

namespace Tests\Feature\Authentication;

use Tests\TestCase;

class ProfileAvatarRouteTest extends TestCase
{
    public function test_avatar_update_route_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/auth/profile/avatar', []);

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }

    public function test_avatar_show_route_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/auth/profile/avatar');

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }
}
