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
        'fpmethod',
        'sourceoffpmethod',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('women_health')) {
            return;
        }

        Schema::table('women_health', function (Blueprint $table) {
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
        if (! Schema::hasTable('women_health')) {
            return;
        }

        Schema::table('women_health', function (Blueprint $table) {
            $table->foreign('family_planning_method_id', 'fpmethod')
                ->references('family_planning_method_id')
                ->on('family_planning_method')
                ->restrictOnDelete()
                ->restrictOnUpdate();
            $table->foreign('source_of_fp_method_id', 'sourceoffpmethod')
                ->references('source_of_fp_method_id')
                ->on('source_of_fp_method')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }
};
