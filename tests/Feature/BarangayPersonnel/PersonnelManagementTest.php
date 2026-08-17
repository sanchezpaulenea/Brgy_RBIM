<?php

namespace Tests\Feature\BarangayPersonnel;

use Tests\TestCase;

class PersonnelManagementTest extends TestCase
{
    public function test_personnel_store_route_accepts_post(): void
    {
        $response = $this->postJson('/api/v1/barangay-personnel', []);

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }

    public function test_personnel_index_route_accepts_get(): void
    {
        $response = $this->getJson('/api/v1/barangay-personnel');

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }
}
