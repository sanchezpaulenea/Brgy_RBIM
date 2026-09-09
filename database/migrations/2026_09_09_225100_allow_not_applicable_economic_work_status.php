<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('economic') || ! Schema::hasColumn('economic', 'status_of_work_business_id')) {
            return;
        }

        Schema::table('economic', function (Blueprint $table) {
            try {
                $table->dropForeign('statusofworkbusiness');
            } catch (\Throwable) {
                // Already dropped or named differently on this database.
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('economic') || ! Schema::hasTable('status_of_work_business')) {
            return;
        }

        Schema::table('economic', function (Blueprint $table) {
            $table->foreign('status_of_work_business_id', 'statusofworkbusiness')
                ->references('status_of_work_business_id')
                ->on('status_of_work_business')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }
};
