<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Previous residence (5 years and 6 months) is no longer collected.
     * Resident type is derived from Since Birth and length of stay.
     */
    public function up(): void
    {
        if (! Schema::hasTable('migration')) {
            return;
        }

        $columns = [
            'previous_residence_6mos_brgy',
            'previous_residence_6mos_city_municipality',
            'previous_residence_5yrs_brgy',
            'previous_residence_5yrs_city_municipality',
        ];

        $present = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn('migration', $column),
        ));

        if ($present === []) {
            return;
        }

        Schema::table('migration', function (Blueprint $table) use ($present): void {
            $table->dropColumn($present);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('migration')) {
            return;
        }

        $missing = array_values(array_filter(
            [
                'previous_residence_6mos_brgy',
                'previous_residence_6mos_city_municipality',
                'previous_residence_5yrs_brgy',
                'previous_residence_5yrs_city_municipality',
            ],
            fn (string $column): bool => ! Schema::hasColumn('migration', $column),
        ));

        if ($missing === []) {
            return;
        }

        Schema::table('migration', function (Blueprint $table) use ($missing): void {
            foreach ($missing as $column) {
                $table->string($column, 45)->nullable();
            }
        });
    }
};
