<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('household')) {
            return;
        }

        if (Schema::hasColumn('household', 'house_lot')) {
            $this->dropAddressIndexes();
            $this->renameLotColumn();
        }

        $this->ensureIntegerColumn();
        $this->ensureIndexes();
    }

    public function down(): void
    {
        if (! Schema::hasTable('household') || ! Schema::hasColumn('household', 'house_number')) {
            return;
        }

        $this->dropAddressIndexes();

        if ($this->isMysql()) {
            DB::statement('ALTER TABLE `household` CHANGE `house_number` `house_lot` VARCHAR(45) NULL');
        } else {
            Schema::table('household', function (Blueprint $table) {
                $table->string('house_lot', 45)->nullable();
            });

            foreach (DB::table('household')->select('household_id', 'house_number')->get() as $row) {
                DB::table('household')->where('household_id', $row->household_id)->update([
                    'house_lot' => $row->house_number === null ? null : (string) $row->house_number,
                ]);
            }

            Schema::table('household', function (Blueprint $table) {
                $table->dropColumn('house_number');
            });
        }

        if (Schema::hasColumn('household', 'house_lot')
            && Schema::hasColumn('household', 'block_num')
            && ! $this->indexExists('uq_lot_blk')) {
            Schema::table('household', function (Blueprint $table) {
                $table->unique(['house_lot', 'block_num'], 'uq_lot_blk');
            });
        }
    }

    private function renameLotColumn(): void
    {
        if ($this->isMysql()) {
            DB::statement("UPDATE `household` SET `house_lot` = NULL WHERE `house_lot` IS NOT NULL AND (`house_lot` NOT REGEXP '^[0-9]+$' OR CHAR_LENGTH(`house_lot`) > 10 OR (CHAR_LENGTH(`house_lot`) = 10 AND `house_lot` > '2147483647'))");
            DB::statement('ALTER TABLE `household` CHANGE `house_lot` `house_number` INT NULL');

            return;
        }

        Schema::table('household', function (Blueprint $table) {
            $table->integer('house_number')->nullable();
        });

        foreach (DB::table('household')->select('household_id', 'house_lot')->get() as $row) {
            $value = is_string($row->house_lot) ? trim($row->house_lot) : $row->house_lot;
            $number = is_string($value) && preg_match('/^\d+$/', $value) === 1 && strlen($value) <= 10
                ? (int) $value
                : (is_int($value) ? $value : null);

            if ($number !== null && $number > 2147483647) {
                $number = null;
            }

            DB::table('household')->where('household_id', $row->household_id)->update([
                'house_number' => $number,
            ]);
        }

        Schema::table('household', function (Blueprint $table) {
            $table->dropColumn('house_lot');
        });
    }

    private function ensureIntegerColumn(): void
    {
        if (! $this->isMysql() || ! Schema::hasColumn('household', 'house_number')) {
            return;
        }

        $column = DB::selectOne("SHOW COLUMNS FROM `household` WHERE Field = 'house_number'");
        $type = strtolower((string) ($column->Type ?? ''));

        if ($type !== '' && ! str_contains($type, 'int')) {
            $this->dropAddressIndexes();
            DB::statement("UPDATE `household` SET `house_number` = NULL WHERE `house_number` IS NOT NULL AND (`house_number` NOT REGEXP '^[0-9]+$' OR CHAR_LENGTH(`house_number`) > 10 OR (CHAR_LENGTH(`house_number`) = 10 AND `house_number` > '2147483647'))");
            DB::statement('ALTER TABLE `household` MODIFY `house_number` INT NULL');
        }
    }

    private function ensureIndexes(): void
    {
        if (! Schema::hasColumn('household', 'house_number')) {
            return;
        }

        if (Schema::hasColumn('household', 'block_num') && ! $this->indexExists('uq_lot_blk')) {
            Schema::table('household', function (Blueprint $table) {
                $table->unique(['house_number', 'block_num'], 'uq_lot_blk');
            });
        }

        if (Schema::hasColumn('household', 'street_id') && ! $this->indexExists('uq_address')) {
            Schema::table('household', function (Blueprint $table) {
                $table->unique(['street_id', 'house_number'], 'uq_address');
            });
        }
    }

    private function dropAddressIndexes(): void
    {
        foreach (['uq_lot_blk', 'uq_address'] as $index) {
            if (! $this->indexExists($index)) {
                continue;
            }

            Schema::table('household', function (Blueprint $table) use ($index) {
                $table->dropUnique($index);
            });
        }
    }

    private function indexExists(string $index): bool
    {
        if (! Schema::hasTable('household')) {
            return false;
        }

        try {
            return Schema::hasIndex('household', $index);
        } catch (\Throwable) {
            return false;
        }
    }

    private function isMysql(): bool
    {
        return in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
