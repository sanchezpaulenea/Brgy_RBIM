<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('action')->upsert([
            ['action_id' => 1, 'action' => 'Create'],
            ['action_id' => 2, 'action' => 'Update'],
            ['action_id' => 3, 'action' => 'View'],
            ['action_id' => 4, 'action' => 'Print'],
            ['action_id' => 5, 'action' => 'Export'],
            ['action_id' => 6, 'action' => 'Backup'],
            ['action_id' => 7, 'action' => 'Restore'],
        ], ['action_id'], ['action']);
    }
}
