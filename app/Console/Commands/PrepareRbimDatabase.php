<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * The core RBIM tables are created from database/brgy_rbim_v2.1.sql rather than
 * Laravel migrations, so `php artisan migrate` cannot be used to adjust them.
 * This command applies the two adjustments the application depends on and is
 * safe to re-run.
 */
class PrepareRbimDatabase extends Command
{
    protected $signature = 'rbim:prepare-database {--pretend : Report what would change without writing}';

    protected $description = 'Seed the "Not applicable" lookup rows and widen the PWD / NCSC-RRN ID columns.';

    /**
     * Lookup tables that need an id-0 row because the referencing column is
     * INT NOT NULL with a foreign key, and the application stores 0 to mean
     * "this question does not apply to the resident".
     *
     * @var array<string, string>
     */
    private const NOT_APPLICABLE_LOOKUPS = [
        'nationality' => 'Not Applicable',
        'religion' => 'Not Applicable',
        'ethnicity' => 'Not Applicable',
        'status_of_work_business' => 'Not Applicable',
        'health_insurance' => 'Not Applicable',
        'facility_visited_past_12mos' => 'Not Applicable',
        'facility_visit_reason' => 'Not Applicable',
        'family_planning_method' => 'None',
        'source_of_fp_method' => 'Not Applicable',
    ];

    /**
     * ID columns that hold government reference numbers too long for INT.
     *
     * @var array<string, string>
     */
    private const WIDENED_ID_COLUMNS = [
        'health.pwd_id_number' => 'PWD ID (16 digits)',
        'sociocivic.ncsc_rrn_id_number' => 'NCSC-RRN (4-12 digits)',
    ];

    public function handle(): int
    {
        $pretend = (bool) $this->option('pretend');

        $this->components->info('Widening government ID columns');
        foreach (self::WIDENED_ID_COLUMNS as $target => $label) {
            [$table, $column] = explode('.', $target);
            $this->widenIdColumn($table, $column, $label, $pretend);
        }

        $this->newLine();
        $this->components->info('Seeding "Not applicable" lookup rows (id 0)');
        foreach (self::NOT_APPLICABLE_LOOKUPS as $table => $label) {
            $this->seedNotApplicableRow($table, $label, $pretend);
        }

        return self::SUCCESS;
    }

    private function widenIdColumn(string $table, string $column, string $label, bool $pretend): void
    {
        $current = $this->columnType($table, $column);

        if ($current === null) {
            $this->components->warn("{$table}.{$column} not found - skipped.");

            return;
        }

        if (str_starts_with(strtolower($current), 'varchar')) {
            $this->components->twoColumnDetail("{$table}.{$column}", "<fg=gray>already {$current}</>");

            return;
        }

        if ($pretend) {
            $this->components->twoColumnDetail("{$table}.{$column}", "<fg=yellow>would widen {$current} -> varchar(45)</>");

            return;
        }

        // Existing numeric values convert to their digit strings, which is what
        // the application now reads back.
        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(45) NULL");
        $this->components->twoColumnDetail("{$table}.{$column} <fg=gray>({$label})</>", "<fg=green>{$current} -> varchar(45)</>");
    }

    private function seedNotApplicableRow(string $table, string $label, bool $pretend): void
    {
        $primaryKey = "{$table}_id";

        if (DB::table($table)->where($primaryKey, 0)->exists()) {
            $this->components->twoColumnDetail($table, '<fg=gray>id 0 already present</>');

            return;
        }

        if ($pretend) {
            $this->components->twoColumnDetail($table, "<fg=yellow>would insert id 0 = \"{$label}\"</>");

            return;
        }

        // MySQL turns an explicit 0 into the next auto-increment value unless
        // NO_AUTO_VALUE_ON_ZERO is active for the session.
        $originalSqlMode = DB::selectOne('SELECT @@SESSION.sql_mode AS mode')->mode;

        try {
            DB::unprepared("SET SESSION sql_mode = '{$originalSqlMode},NO_AUTO_VALUE_ON_ZERO'");
            DB::table($table)->insert([
                $primaryKey => 0,
                $table => $label,
            ]);
        } catch (Throwable $exception) {
            $this->components->error("{$table}: {$exception->getMessage()}");

            return;
        } finally {
            DB::unprepared("SET SESSION sql_mode = '{$originalSqlMode}'");
        }

        $this->components->twoColumnDetail($table, "<fg=green>inserted id 0 = \"{$label}\"</>");
    }

    private function columnType(string $table, string $column): ?string
    {
        $row = DB::selectOne(
            'SELECT COLUMN_TYPE AS type
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [DB::getDatabaseName(), $table, $column]
        );

        return $row?->type;
    }
}
