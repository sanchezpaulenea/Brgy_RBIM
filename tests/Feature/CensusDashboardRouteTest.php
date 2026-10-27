<?php

namespace Tests\Feature;

use Tests\TestCase;

class CensusDashboardRouteTest extends TestCase
{
    public function test_tile_and_statistic_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/dashboard/tiles')->assertUnauthorized();
        $this->getJson('/api/v1/dashboard/stats/residents')->assertUnauthorized();
        $this->getJson('/api/v1/dashboard/stats/residents/export')->assertUnauthorized();
    }
}
