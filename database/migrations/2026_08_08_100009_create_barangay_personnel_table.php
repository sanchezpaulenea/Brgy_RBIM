<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangay_personnel', function (Blueprint $table) {
            $table->integer('personnel_id')->autoIncrement()->primary();
            $table->integer('position_id');
            $table->string('personnel_last_name', 45);
            $table->string('personnel_first_name', 45);
            $table->string('personnel_middle_name', 45)->nullable();
            $table->string('personnel_suffix', 10)->nullable();
            $table->integer('personnel_status_id')->default(1);
            $table->date('personnel_date_of_birth');

            $table->foreign('position_id', 'personnel_position')
                ->references('position_id')
                ->on('personnel_position')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreign('personnel_status_id', 'personnel_status')
                ->references('personnel_status_id')
                ->on('personnel_status')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });

        DB::unprepared('DROP TRIGGER IF EXISTS `trg_personnel_dob_insert`');

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_personnel_dob_insert` BEFORE INSERT ON `barangay_personnel`
FOR EACH ROW
BEGIN
    IF NEW.personnel_date_of_birth > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Date of birth cannot be in the future.';
    END IF;
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_personnel_dob_insert`');

        Schema::dropIfExists('barangay_personnel');
    }
};
