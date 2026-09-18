<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $addressColumns = ['block_num', 'building_name', 'unit_num'];

    public function up(): void
    {
        if (! Schema::hasTable('household')) {
            return;
        }

        if (! Schema::hasColumn('household', 'block_num')) {
            Schema::table('household', function (Blueprint $table) {
                $table->string('block_num', 45)->nullable()->after('house_lot');
            });
        }

        if (! Schema::hasColumn('household', 'building_name')) {
            Schema::table('household', function (Blueprint $table) {
                $table->string('building_name', 45)->nullable()->after('block_num');
            });
        }

        if (! Schema::hasColumn('household', 'unit_num')) {
            Schema::table('household', function (Blueprint $table) {
                $table->string('unit_num', 45)->nullable()->after('building_name');
            });
        }

        if (! Schema::hasIndex('household', 'uq_lot_blk')) {
            Schema::table('household', function (Blueprint $table) {
                $table->unique(['house_lot', 'block_num'], 'uq_lot_blk');
            });
        }

        if ($this->isMysql() && Schema::hasColumn('household', 'number_of_house_story')) {
            DB::statement('ALTER TABLE `household` MODIFY `number_of_house_story` INT NOT NULL DEFAULT 1');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('household')) {
            return;
        }

        if (Schema::hasIndex('household', 'uq_lot_blk')) {
            Schema::table('household', function (Blueprint $table) {
                $table->dropUnique('uq_lot_blk');
            });
        }

        $drop = array_values(array_filter(
            $this->addressColumns,
            fn (string $column): bool => Schema::hasColumn('household', $column),
        ));

        if ($drop !== []) {
            Schema::table('household', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }

        if ($this->isMysql() && Schema::hasColumn('household', 'number_of_house_story')) {
            DB::statement('ALTER TABLE `household` MODIFY `number_of_house_story` INT NOT NULL');
        }
    }

    private function isMysql(): bool
    {
        return in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
