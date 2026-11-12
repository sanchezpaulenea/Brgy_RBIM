<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TRUNCATED = 'Presence of relatives and friends in other pl';

    private const FULL = 'Presence of relatives and friends in other places';

    public function up(): void
    {
        $this->expand('reason_for_transfer', 'reason_for_transfer');
        $this->expand('reason_for_leaving', 'reason_for_leaving');
    }

    public function down(): void
    {
        $this->restore('reason_for_transfer', 'reason_for_transfer');
        $this->restore('reason_for_leaving', 'reason_for_leaving');
    }

    private function expand(string $table, string $column): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(100) NOT NULL");
        }

        DB::table($table)
            ->where($column, self::TRUNCATED)
            ->update([$column => self::FULL]);
    }

    private function restore(string $table, string $column): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)
            ->where($column, self::FULL)
            ->update([$column => self::TRUNCATED]);

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(45) NOT NULL");
        }
    }
};
