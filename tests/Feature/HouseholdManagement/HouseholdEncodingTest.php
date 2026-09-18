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

    public function test_household_assessment_index_route_accepts_get(): void
    {
        $response = $this->getJson('/api/v1/households/1/assessments');

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }

    public function test_household_assessment_store_route_accepts_post(): void
    {
        $response = $this->postJson('/api/v1/households/1/assessments', []);

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }

    public function test_household_assessment_options_route_accepts_get(): void
    {
        $response = $this->getJson('/api/v1/household-assessment-options');

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }

    public function test_household_questions_store_route_accepts_post(): void
    {
        $response = $this->postJson('/api/v1/households/1/questions', []);

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }

    public function test_household_questions_update_route_accepts_patch(): void
    {
        $response = $this->patchJson('/api/v1/household-questions/1', []);

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }
}
