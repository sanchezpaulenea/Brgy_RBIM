<?php

namespace Tests\Unit;

use App\Support\CensusStatisticCatalog;
use Tests\TestCase;

class CensusStatisticCatalogTest extends TestCase
{
    public function test_sensitive_statistics_can_be_hidden_without_removing_the_rest(): void
    {
        $all = CensusStatisticCatalog::all();
        $visible = CensusStatisticCatalog::visible(hideSensitive: true);

        $this->assertArrayHasKey('residents', $all);
        $this->assertArrayHasKey('pwd', $all);
        $this->assertArrayHasKey('solo-parents', $all);
        $this->assertArrayHasKey('residents', $visible);
        $this->assertArrayHasKey('solo-parents', $visible);
        $this->assertArrayNotHasKey('pwd', $visible);
    }
}
