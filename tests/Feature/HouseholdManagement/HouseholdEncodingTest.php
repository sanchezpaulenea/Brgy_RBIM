<?php

namespace Tests\Feature\HouseholdManagement;

use Tests\TestCase;

class HouseholdEncodingTest extends TestCase
{
    public function test_location_profile_route_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/location-profile');

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }

    public function test_household_store_route_accepts_post(): void
    {
        $response = $this->postJson('/api/v1/households', []);

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }

    public function test_resident_store_route_accepts_post(): void
    {
        $response = $this->postJson('/api/v1/residents', []);

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }
}
