<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $placeholders = ['n/a', 'na', 'n.a', 'n.a.', 'not applicable'];

    public function up(): void
    {
        if (! Schema::hasTable('household')) {
            return;
        }

        foreach (['house_lot', 'block_num', 'building_name', 'unit_num'] as $column) {
            if (! Schema::hasColumn('household', $column)) {
                continue;
            }

            DB::table('household')
                ->where(function ($query) use ($column) {
                    $query->where($column, '')
                        ->orWhereRaw('LOWER(TRIM(`'.$column.'`)) in (?, ?, ?, ?, ?)', $this->placeholders);
                })
                ->update([$column => null]);
        }
    }

    public function down(): void
    {
        // Placeholder values were stored as null because they are not unique addresses.
    }
};
