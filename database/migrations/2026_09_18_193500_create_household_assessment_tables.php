<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('census_status')) {
            Schema::create('census_status', function (Blueprint $table) {
                $table->integer('census_status_id')->autoIncrement()->primary();
                $table->string('status_code', 45);
                $table->string('status_name', 45);

                $table->unique('status_code', 'uq_census_status_code');
            });
        }

        DB::table('census_status')->upsert([
            ['census_status_id' => 1, 'status_code' => 'C', 'status_name' => 'Completed'],
            ['census_status_id' => 2, 'status_code' => 'CB', 'status_name' => 'Callback'],
            ['census_status_id' => 3, 'status_code' => 'R', 'status_name' => 'Refused'],
        ], ['census_status_id'], ['status_code', 'status_name']);

        if (Schema::hasTable('household_assessment')) {
            return;
        }

        Schema::create('household_assessment', function (Blueprint $table) {
            $table->integer('assessment_id')->autoIncrement()->primary();
            $table->integer('household_id');
            $table->integer('census_status_id');
            $table->dateTime('visit_start');
            $table->dateTime('visit_end');
            $table->date('next_visit_date')->nullable();
            $table->integer('interviewer_id');
            $table->integer('supervisor_id');
            $table->integer('encoder_id');
            $table->integer('previous_assessment_id')->nullable();

            $table->foreign('household_id', 'assessment_household')
                ->references('household_id')
                ->on('household')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('census_status_id', 'assessment_census_status')
                ->references('census_status_id')
                ->on('census_status')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('interviewer_id', 'assessment_interviewer')
                ->references('personnel_id')
                ->on('barangay_personnel')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('supervisor_id', 'assessment_supervisor')
                ->references('personnel_id')
                ->on('barangay_personnel')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('encoder_id', 'assessment_encoder')
                ->references('personnel_id')
                ->on('barangay_personnel')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('previous_assessment_id', 'assessment_previous')
                ->references('assessment_id')
                ->on('household_assessment')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_assessment');
        Schema::dropIfExists('census_status');
    }
};
