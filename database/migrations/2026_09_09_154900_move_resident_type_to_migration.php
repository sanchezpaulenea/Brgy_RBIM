<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('migration') || ! $this->isMysql()) {
            return;
        }

        if (! Schema::hasColumn('migration', 'resident_type_id') && Schema::hasTable('resident_type')) {
            Schema::table('migration', function (Blueprint $table) {
                $table->integer('resident_type_id')->after('date_of_transfer_in_brgy');
                $table->foreign('resident_type_id', 'residenttype')
                    ->references('resident_type_id')
                    ->on('resident_type')
                    ->restrictOnDelete()
                    ->restrictOnUpdate();
            });
        }

        $this->makeForeignNullable(
            'reason_for_leaving_id',
            'reasonforleaving',
            'reason_for_leaving',
            'reason_for_leaving_id',
        );
        $this->makeForeignNullable(
            'reason_for_transfer_id',
            'reasonfortransfer',
            'reason_for_transfer',
            'reason_for_transfer_id',
        );

        if (Schema::hasColumn('migration', 'will_return_to_previous_residence')) {
            DB::statement('ALTER TABLE `migration` MODIFY `will_return_to_previous_residence` TINYINT(1) NULL');
        }
    }

    public function down(): void
    {
        // Skip-pattern columns stay nullable so existing non-migrant rows remain valid.
    }

    private function isMysql(): bool
    {
        return in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function makeForeignNullable(string $column, string $foreignName, string $referencedTable, string $referencedColumn): void
    {
        if (! Schema::hasColumn('migration', $column) || ! Schema::hasTable($referencedTable)) {
            return;
        }

        Schema::table('migration', function (Blueprint $table) use ($foreignName) {
            try {
                $table->dropForeign($foreignName);
            } catch (\Throwable) {
                // Foreign key may already have been dropped.
            }
        });

        DB::statement('ALTER TABLE `migration` MODIFY `'.$column.'` INT NULL');

        Schema::table('migration', function (Blueprint $table) use ($column, $foreignName, $referencedTable, $referencedColumn) {
            $table->foreign($column, $foreignName)
                ->references($referencedColumn)
                ->on($referencedTable)
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }
};
