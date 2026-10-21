<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardStatisticsRouteTest extends TestCase
{
    public function test_dashboard_statistics_route_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/dashboard/statistics');

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }
}
