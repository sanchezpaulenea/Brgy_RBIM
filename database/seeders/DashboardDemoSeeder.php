<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sample households, residents, and questionnaire answers for previewing the
 * dashboard. Demo households use house numbers from 9001 up so they can be
 * told apart from real records; rerunning replaces the previous demo rows.
 *
 * Run:    php artisan db:seed --class=DashboardDemoSeeder
 * Remove: php artisan tinker --execute="Database\Seeders\DashboardDemoSeeder::purge();"
 */
class DashboardDemoSeeder extends Seeder
{
    public const FIRST_HOUSE_NUMBER = 9001;

    private const HOUSEHOLDS = 60;

    private const LAST_NAMES = ['Bacani', 'Dulnuan', 'Palangdan', 'Bayawa', 'Cosalan', 'Ganggangan', 'Kimayong', 'Lumbaga', 'Agpaoa', 'Tadeo', 'Sagubo', 'Bangyay', 'Calpito', 'Dalisdis', 'Esteban', 'Fianza', 'Gawidan', 'Likigan', 'Mendoza', 'Santos'];

    private const MALE_NAMES = ['Juan', 'Pedro', 'Marlon', 'Ricky', 'Benjie', 'Jomar', 'Arnel', 'Dexter', 'Noel', 'Rodel', 'Kevin', 'Jayson', 'Ramon', 'Elmer', 'Gabriel'];

    private const FEMALE_NAMES = ['Maria', 'Ana', 'Josephine', 'Rowena', 'Grace', 'Liza', 'Marites', 'Jenny', 'Kristine', 'Shiela', 'Angel', 'Joy', 'Rosalie', 'Elena', 'Cherry'];

    public function run(): void
    {
        mt_srand(2026);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::transaction(function (): void {
                self::purge();

                for ($i = 0; $i < self::HOUSEHOLDS; $i++) {
                    $this->seedHousehold(self::FIRST_HOUSE_NUMBER + $i);
                }
            });
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public static function purge(): void
    {
        $households = DB::table('household')
            ->where('house_number', '>=', self::FIRST_HOUSE_NUMBER)
            ->pluck('household_id');

        if ($households->isEmpty()) {
            return;
        }

        $questions = DB::table('household_questions')->whereIn('household_id', $households)->pluck('household_questions_id');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::table('common_disease')->whereIn('household_question_id', $questions)->delete();
            DB::table('primary_need')->whereIn('household_question_id', $questions)->delete();
            DB::table('household_questions')->whereIn('household_questions_id', $questions)->delete();
            DB::table('resident')->whereIn('household_id', $households)->delete();
            DB::table('household')->whereIn('household_id', $households)->delete();
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function seedHousehold(int $houseNumber): void
    {
        $clan = $this->pick([1 => 16, 2 => 12, 3 => 10, 4 => 8, 5 => 6, 6 => 4]);

        $householdId = DB::table('household')->insertGetId([
            'clan_id' => $clan,
            'head_resident_id' => 0,
            'street_id' => $this->pick([1 => 18, 2 => 15, 3 => 12, 4 => 8]),
            'number_of_house_story' => $this->pick([1 => 35, 2 => 18, 3 => 6, 4 => 1]),
            'number_of_basement_level' => $this->pick([0 => 45, 1 => 12, 2 => 3]),
            'house_number' => $houseNumber,
            'registration_date' => now()->subDays(mt_rand(1, 700))->toDateTimeString(),
            'household_status_id' => $this->pick([1 => 50, 2 => 6, 3 => 4]),
        ]);

        $headId = $this->seedResidents($householdId, $clan);

        DB::table('household')->where('household_id', $householdId)->update(['head_resident_id' => $headId]);

        if (mt_rand(1, 100) <= 85) {
            $this->seedQuestions($householdId);
        }
    }

    private function seedResidents(int $householdId, int $clan): int
    {
        $lastName = self::LAST_NAMES[array_rand(self::LAST_NAMES)];
        $headSex = $this->pick([1 => 70, 2 => 30]);
        $married = mt_rand(1, 100) <= 75;

        $members = [[1, $headSex, mt_rand(28, 70), $married ? $this->pick([2 => 80, 3 => 20]) : $this->pick([1 => 40, 4 => 35, 5 => 20, 6 => 5])]];

        if ($married) {
            $members[] = [2, $headSex === 1 ? 2 : 1, mt_rand(25, 68), $members[0][3]];
        }

        for ($child = 0, $children = mt_rand(0, 4); $child < $children; $child++) {
            $sex = $this->pick([1 => 50, 2 => 50]);
            $age = mt_rand(0, 30);
            $members[] = [$sex === 1 ? 3 : 4, $sex, $age, $age >= 22 ? $this->pick([1 => 70, 2 => 20, 3 => 10]) : 1];
        }

        if (mt_rand(1, 100) <= 20) {
            $sex = $this->pick([1 => 40, 2 => 60]);
            $members[] = [$sex === 1 ? 11 : 12, $sex, mt_rand(65, 90), $this->pick([4 => 60, 2 => 40])];
        }

        if (mt_rand(1, 100) <= 15) {
            $sex = $this->pick([1 => 50, 2 => 50]);
            $members[] = [$sex === 1 ? 9 : 10, $sex, mt_rand(0, 10), 1];
        }

        $headId = 0;

        foreach ($members as [$relationship, $sex, $age, $marital]) {
            $names = $sex === 1 ? self::MALE_NAMES : self::FEMALE_NAMES;

            $id = DB::table('resident')->insertGetId([
                'last_name' => $lastName,
                'first_name' => $names[array_rand($names)],
                'relationship_to_hh_id' => $relationship,
                'sex_id' => $sex,
                'date_of_birth' => now()->subYears($age)->subDays(mt_rand(0, 360))->toDateString(),
                'birth_city_municipality' => 'Baguio City',
                'birth_province' => 'Benguet',
                'birth_country' => 'Philippines',
                'nationality_id' => $this->pick([1 => 92, 2 => 3, 3 => 2, 4 => 2, 5 => 1]),
                'religion_id' => $this->pick([1 => 70, 2 => 15, 3 => 15]),
                'ethnicity_id' => $this->pick([3 => 40, 4 => 30, 2 => 20, 1 => 10]),
                'marital_status_id' => $marital,
                'clan_id' => $clan,
                'resident_status_id' => $this->pick([1 => 92, 2 => 5, 3 => 3]),
                'household_id' => $householdId,
            ]);

            if ($relationship === 1) {
                $headId = $id;
            }
        }

        return $headId;
    }

    private function seedQuestions(int $householdId): void
    {
        $unit = $this->pick([4 => 30, 3 => 12, 2 => 6, 1 => 2]);

        $questionId = DB::table('household_questions')->insertGetId([
            'household_id' => $householdId,
            'ownership_of_housing_unit_id' => $unit,
            'ownership_of_lot_id' => mt_rand(1, 100) <= 70 ? $unit : $this->pick([4 => 10, 3 => 20, 2 => 15, 1 => 5]),
            'fuel_type_for_lighting_id' => $this->pick([4 => 45, 3 => 8, 1 => 3, 5 => 2]),
            'fuel_type_for_cooking_id' => $this->pick([2 => 35, 4 => 10, 3 => 8, 1 => 4, 5 => 3]),
            'main_source_drinking_water_id' => $this->pick([10 => 20, 9 => 15, 11 => 10, 8 => 5, 4 => 4, 2 => 3, 1 => 1]),
            'kitchen_garbage_disposal_id' => $this->pick([6 => 25, 3 => 12, 4 => 10, 1 => 5, 2 => 4, 5 => 2]),
            'perform_garbage_seggragation' => mt_rand(1, 100) <= 65 ? 1 : 0,
            'toilet_facility_type_id' => $this->pick([6 => 28, 5 => 10, 4 => 8, 3 => 5, 2 => 3, 1 => 1]),
            'type_of_building_house_id' => $this->pick([1 => 40, 2 => 8, 3 => 5, 4 => 2]),
            'construction_material_outer_wall_id' => $this->pick([9 => 22, 7 => 15, 8 => 12, 6 => 6, 5 => 3, 2 => 2]),
        ]);

        foreach ($this->pickMany([1 => 45, 2 => 25, 3 => 30], mt_rand(0, 2)) as $disease) {
            DB::table('common_disease')->insert(['household_question_id' => $questionId, 'common_disease_id' => $disease]);
        }

        foreach ($this->pickMany([1 => 45, 3 => 35, 2 => 20], mt_rand(1, 2)) as $need) {
            DB::table('primary_need')->insert(['household_question_id' => $questionId, 'primary_need_id' => $need]);
        }
    }

    /**
     * @param  array<int, int>  $weights
     */
    private function pick(array $weights): int
    {
        $roll = mt_rand(1, array_sum($weights));

        foreach ($weights as $value => $weight) {
            $roll -= $weight;

            if ($roll <= 0) {
                return $value;
            }
        }

        return array_key_first($weights);
    }

    /**
     * @param  array<int, int>  $weights
     * @return list<int>
     */
    private function pickMany(array $weights, int $count): array
    {
        $picked = [];

        while (count($picked) < min($count, count($weights))) {
            $value = $this->pick($weights);
            $picked[$value] = $value;
            unset($weights[$value]);
        }

        return array_values($picked);
    }
}
