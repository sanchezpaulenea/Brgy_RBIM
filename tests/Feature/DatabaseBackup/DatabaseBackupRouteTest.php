<?php

namespace Tests\Feature\DatabaseBackup;

use Tests\TestCase;

class DatabaseBackupRouteTest extends TestCase
{
    public function test_export_route_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/database-backups/export', [
            'format' => 'sql',
        ]);

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }

    public function test_restore_route_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/database-backups/restore', [
            'confirmation' => 'RESTORE',
        ]);

        $this->assertNotSame(405, $response->status());
        $response->assertUnauthorized();
    }
}
