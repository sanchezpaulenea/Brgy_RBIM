<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonnelPositionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('personnel_position')->upsert([
            ['position_id' => 1, 'position_name' => 'Barangay Secretary'],
            ['position_id' => 2, 'position_name' => 'SK Chairperson'],
        ], ['position_id'], ['position_name']);
    }
}
