<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangay_personnel', function (Blueprint $table) {
            $table->integer('sex_id')->nullable()->after('personnel_date_of_birth');

            if (Schema::hasTable('sex')) {
                $table->foreign('sex_id', 'barangay_personnel_sex')->references('sex_id')->on('sex');
            }
        });
    }

    public function down(): void
    {
        Schema::table('barangay_personnel', function (Blueprint $table) {
            if (Schema::hasTable('sex')) {
                $table->dropForeign('barangay_personnel_sex');
            }

            $table->dropColumn('sex_id');
        });
    }
};
