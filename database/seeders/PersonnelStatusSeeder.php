<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonnelStatusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('personnel_status')->upsert([
            ['personnel_status_id' => 1, 'personnel_status' => 'Active'],
            ['personnel_status_id' => 2, 'personnel_status' => 'Inactive'],
            ['personnel_status_id' => 3, 'personnel_status' => 'Resigned'],
            ['personnel_status_id' => 4, 'personnel_status' => 'Retired'],
            ['personnel_status_id' => 5, 'personnel_status' => 'On Leave'],
        ], ['personnel_status_id'], ['personnel_status']);
    }
}
