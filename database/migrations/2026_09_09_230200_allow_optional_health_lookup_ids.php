<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $constraints = [
        'healthinsurance',
        'facilityvisited',
        'facilityvisitreason',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('health')) {
            return;
        }

        Schema::table('health', function (Blueprint $table) {
            foreach ($this->constraints as $name) {
                try {
                    $table->dropForeign($name);
                } catch (\Throwable) {
                    // Already dropped or named differently on this database.
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('health')) {
            return;
        }

        Schema::table('health', function (Blueprint $table) {
            $table->foreign('health_insurance_id', 'healthinsurance')
                ->references('health_insurance_id')
                ->on('health_insurance')
                ->restrictOnDelete()
                ->restrictOnUpdate();
            $table->foreign('facility_visited_past_12mos_id', 'facilityvisited')
                ->references('facility_visited_past_12mos_id')
                ->on('facility_visited_past_12mos')
                ->restrictOnDelete()
                ->restrictOnUpdate();
            $table->foreign('facility_visit_reason_id', 'facilityvisitreason')
                ->references('facility_visit_reason_id')
                ->on('facility_visit_reason')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }
};
