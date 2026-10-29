<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per parent. Application checks can both pass when two accounts
     * save at the same time; the unique index makes the second insert fail.
     *
     * @var array<string, array{column: string, index: string}>
     */
    private array $uniqueParents = [
        'education' => ['column' => 'resident_id', 'index' => 'uq_education_resident'],
        'economic' => ['column' => 'resident_id', 'index' => 'uq_economic_resident'],
        'health' => ['column' => 'resident_id', 'index' => 'uq_health_resident'],
        'infant_health' => ['column' => 'resident_id', 'index' => 'uq_infant_health_resident'],
        'migration' => ['column' => 'resident_id', 'index' => 'uq_migration_resident'],
        'skills_development' => ['column' => 'resident_id', 'index' => 'uq_skills_resident'],
        'sociocivic' => ['column' => 'resident_id', 'index' => 'uq_sociocivic_resident'],
        'community_tax_cert' => ['column' => 'resident_id', 'index' => 'uq_ctc_resident'],
        'women_health' => ['column' => 'health_id', 'index' => 'uq_women_health'],
        'household_questions' => ['column' => 'household_id', 'index' => 'uq_household_questions'],
    ];

    public function up(): void
    {
        foreach ($this->uniqueParents as $table => $unique) {
            $this->addUnique($table, $unique['column'], $unique['index']);
        }
    }

    public function down(): void
    {
        foreach ($this->uniqueParents as $table => $unique) {
            if (! Schema::hasTable($table) || ! $this->hasIndex($table, $unique['index'])) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($unique): void {
                $blueprint->dropUnique($unique['index']);
            });
        }
    }

    private function addUnique(string $table, string $column, string $index): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column) || $this->hasIndex($table, $index)) {
            return;
        }

        $duplicates = DB::table($table)
            ->select($column)
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicates) {
            throw new RuntimeException("Cannot add {$index} because {$table}.{$column} already has duplicate rows.");
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $index): void {
            $blueprint->unique($column, $index);
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $existing): bool => ($existing['name'] ?? null) === $index);
    }
};
